"""Run ONLY against a disposable import of original.sql. All record writes use HTTP forms.
BAR_TEST_URL=http://127.0.0.1:8080/ python3 tests/http_workflows.py
This test creates fictional records, including an approved enrollment. Discard the test database afterward.
"""
import html, http.cookiejar, os, re, urllib.parse, urllib.request, urllib.error
BASE = os.environ.get('BAR_TEST_URL', 'http://127.0.0.1:8080/')
checks = 0

def check(ok, label):
    global checks
    assert ok, label
    checks += 1
    print('PASS', label)

class Client:
    def __init__(self):
        self.opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
        self.token = ''
        self.role = 'student'
    def request(self, page, data=None, **params):
        # Test-side URL map; the server uses physical pages in role-specific folders, with no page dispatcher.
        if page.endswith(('.php', '.css', '.js', '.sql', '.svg')) or '/' in page or page.startswith('.'):
            filename = page
        elif page in ('records','edit','save','delete'):
            resource = params.pop('resource', None) or (data or {}).get('resource')
            singular = dict(accounts='account', students='student', subjects='subject', courses='program', sections='section', schedules='schedule')[resource]
            id = int(params.get('id') or (data or {}).get('id') or 0)
            if page in ('edit','save'):
                filename = singular + ('-edit.php' if id else '-create.php')
                if id: params['id'] = id
            else:
                filename = singular + 's.php'
        else:
            filename = {
                'login':'index.php', 'dashboard':'admin-dashboard.php' if self.role == 'admin' else 'student-dashboard.php',
                'logout':'logout.php', 'submit':'enroll.php', 'withdraw':'my-enrollments.php',
                'profile':'my-profile.php', 'settings':'account-settings.php', 'password':'account-settings.php',
                'enrollments':'enrollment-requests.php' if self.role == 'admin' else 'my-enrollments.php',
                'enrollment':'enrollment-review.php' if self.role == 'admin' else 'enrollment-record.php',
                'approve':'enrollment-review.php', 'remove-pending':'enrollment-review.php',
                'revise':'enrollment-correct.php', 'edit-enrollment':'enrollment-correct.php',
            }[page]
            if page in ('approve','remove-pending','revise'):
                params['id'] = data['id']
                if page != 'revise': data = dict(data, action='approve' if page == 'approve' else 'remove')
        locations = {'accounts.php': 'Admin/accounts.php', 'account-create.php': 'Admin/account-create.php', 'account-edit.php': 'Admin/account-edit.php', 'students.php': 'Admin/students.php', 'student-create.php': 'Admin/student-create.php', 'student-edit.php': 'Admin/student-edit.php', 'subjects.php': 'Admin/subjects.php', 'subject-create.php': 'Admin/subject-create.php', 'subject-edit.php': 'Admin/subject-edit.php', 'programs.php': 'Admin/programs.php', 'program-create.php': 'Admin/program-create.php', 'program-edit.php': 'Admin/program-edit.php', 'sections.php': 'Admin/sections.php', 'section-create.php': 'Admin/section-create.php', 'section-edit.php': 'Admin/section-edit.php', 'schedules.php': 'Admin/schedules.php', 'schedule-create.php': 'Admin/schedule-create.php', 'schedule-edit.php': 'Admin/schedule-edit.php', 'index.php': 'index.php', 'admin-dashboard.php': 'Admin/admin-dashboard.php', 'student-dashboard.php': 'student/student-dashboard.php', 'my-profile.php': 'student/my-profile.php', 'enroll.php': 'student/enroll.php', 'my-enrollments.php': 'student/my-enrollments.php', 'enrollment-requests.php': 'Admin/enrollment-requests.php', 'enrollment-review.php': 'Admin/enrollment-review.php', 'enrollment-correct.php': 'Admin/enrollment-correct.php', 'enrollment-record.php': 'pages/enrollment-record.php', 'account-settings.php': 'pages/account-settings.php', 'logout.php': 'logout.php'}
        filename = locations.get(filename, filename)
        if filename in ('app.js', 'favicon.svg'): filename = 'assets/' + filename
        url = BASE.rstrip('/') + '/' + filename + ('?' + urllib.parse.urlencode(params) if params else '')
        payload = None if data is None else urllib.parse.urlencode(data, doseq=True).encode()
        try:
            response = self.opener.open(url, payload)
        except urllib.error.HTTPError as error:
            response = error
        text = response.read().decode()
        token = re.search(r'name="csrf" value="([a-f0-9]+)"', text)
        if token: self.token = token[1]
        return response.code, text
    def post(self, page, data):
        return self.request(page, dict(csrf=self.token, **data))
    def login(self, username, password):
        self.role = 'admin' if username == 'admins' else 'student'
        self.request('login')
        return self.post('login', dict(username=username, password=password))
    def save(self, resource, values, id=0):
        code, text = self.post('save', dict(resource=resource, id=id, **values))
        check(code == 200 and 'Record saved.' in text, resource + ' save')
        singular = dict(accounts='account', students='student', subjects='subject', courses='program', sections='section', schedules='schedule')[resource]
        match = re.search(singular + r'-edit\.php\?id=(\d+)', text)
        return int(match[1])

admin, student, legacy, anonymous = Client(), Client(), Client(), Client()
check(anonymous.request('dashboard')[0] == 200, 'anonymous redirected to login')
code, text = admin.login("' OR 1=1 --", 'wrong')
check('incorrect' in text, 'SQL injection login rejected')
check('Your campus at a glance' in admin.login('admins', 'admins')[1], 'legacy admin sign-in')
for resource in ['accounts','students','subjects','courses','sections','schedules']:
    code, text = admin.request('records', resource=resource)
    check(code == 200 and 'directory' in text, resource + ' directory loads')
code, text = admin.request('save', dict(csrf='bad', resource='accounts'))
check(code == 419, 'CSRF rejection')
account = admin.save('accounts', dict(username='wbtest1', password='Testbar123', fullname='WinBAR Student', user_type='student'))
_, text = admin.post('save', dict(resource='accounts', id=0, username='wbtest1', password='Testbar123', fullname='Duplicate', user_type='student'))
check('already in use' in text, 'duplicate username rejected')
fields = dict(accountID=str(account), studnum='WB-0001', fname='WinBAR', minitial='B', lname='Student', course='wbtest', yearlvl='II-4')
profile = admin.save('students', fields)
_, text = admin.post('save', dict(resource='students', id=profile, **(fields | {'emailaddress':'verylong@example.com'})))
check('14 characters' in text, 'long email rejected without truncation')
check('WB-0001' in text, 'failed form retains non-secret inputs')
program = admin.save('courses', dict(course_title='wbtest', course_desc='WinBAR test program'))
subject_values = dict(subjectcode='WB101', subjecttitle='<b>Safe output</b>', numofunits='3', room='101', time='09:00-10:00', instructor='Test Faculty', day='Monday', islab='0', istutorial='0', description='Test only', course_con='wbtest')
subject = admin.save('subjects', subject_values)
_, text = admin.request('records', resource='subjects', q='WB101')
check('&lt;b&gt;Safe output&lt;/b&gt;' in text and '<b>Safe output</b>' not in text, 'stored HTML escaped')
section = admin.save('sections', dict(section_name='WB-2A', section_course='wbtest', yr_level='2', min_num='1', max_num='40'))
schedule_values = dict(section_ID=str(section), subject_ID=str(subject), Instructor_ID='1', time_from='09:00', time_to='10:00', room_ID='1')
schedule = admin.save('schedules', schedule_values)
admin.save('schedules', schedule_values | {'time_to':'11:00'}, schedule)
_, text = admin.post('save', dict(resource='subjects', id=subject, **(subject_values | {'course_con':'different'})))
check('conflict with this subject' in text, 'subject program change respects existing schedules')
_, text = admin.post('save', dict(resource='sections', id=section, section_name='WB-2A', section_course='different', yr_level='2', min_num='1', max_num='40'))
check('conflict with this section' in text, 'section program change respects existing schedules')
for resource, record_id in [('accounts',account), ('students',profile), ('subjects',subject), ('courses',program), ('sections',section), ('schedules',schedule)]:
    check(admin.request('edit', resource=resource)[0] == 200, resource + ' create screen')
    code, form = admin.request('edit', resource=resource, id=record_id)
    check(code == 200 and 'Save ' in form, resource + ' edit screen')
check(admin.request('logout.php')[0] == 405, 'logout requires POST')
for path in ['original.sql', 'conn.php', 'app/helpers.php', 'config/app.example.php', 'docs/page-inventory.json']:
    check(anonymous.request(path)[0] == 403, 'development server protects ' + path)
for path in ['main.css', 'app.js', 'favicon.svg']:
    check(anonymous.request(path)[0] == 200, 'shared asset loads: ' + path)
check(admin.request('my-profile.php')[0] == 403, 'admin cannot enter student-only profile page')
check('Hello,' in student.login('wbtest1','Testbar123')[1], 'new student sign-in')
check(student.request('records', resource='accounts')[0] == 403, 'student cannot access admin directory')
check(student.post('delete', dict(resource='accounts', id=account))[0] == 403, 'student cannot mutate admin records')
check(student.request('enrollment', id=2)[0] == 404, 'student cannot read another enrollment')
check(student.request('admin-dashboard.php')[0] == 403, 'student cannot enter admin dashboard')
check(student.request('enrollment-requests.php')[0] == 403, 'student cannot enter registrar request directory')
check(student.request('enroll.php')[0] == 200, 'subject selection screen')
check(student.request('my-profile.php')[0] == 200, 'student profile screen')
check(student.request('enrollment-correct.php', id=2)[0] == 403, 'student cannot enter correction screen')
_, text = student.post('submit', {'subjects[]':['999999']})
check('no longer available' in text, 'unknown subject rejected')
_, text = student.post('submit', {'subjects[]':['1']})
check('only subjects in your program' in text, 'cross-program subject rejected')
_, text = student.post('submit', {'subjects[]':[str(subject),str(subject)]})
check('distinct subjects' in text, 'duplicate selections rejected')
_, text = student.post('submit', {'subjects[]':[str(subject)], 'totalunits':'999', 'studentID':'2014-052', 'approved':'1'})
check('ready for registrar review' in text and '>3</td>' in text and 'Pending' in text, 'server owns student identity, approval and unit totals')
enrollment = int(re.search(r'enrollment-record\.php\?id=(\d+)', text)[1])
_, text = student.post('submit', {'subjects[]':[str(subject)]})
check('already have an enrollment' in text, 'repeat submission rejected')
_, text = admin.post('delete', dict(resource='subjects', id=subject))
check('Enrollment records refer' in text, 'referenced subject deletion blocked')
_, text = admin.post('save', dict(resource='subjects', id=subject, **(subject_values | {'subjectcode':'RENAMED'})))
check('locked while enrollment' in text, 'enrollment subject code protected')
_, text = student.post('withdraw', {'id':enrollment})
check('Pending request withdrawn' in text, 'student withdraws own pending request')
_, text = student.post('submit', {'subjects[]':[str(subject)]})
enrollment = int(re.search(r'enrollment-record\.php\?id=(\d+)', text)[1])
check(admin.request('enrollment-correct.php', id=enrollment)[0] == 200, 'pending correction form renders')
check(admin.request('enrollment-record.php', id=enrollment)[0] == 200, 'registrar printable record renders')
code, review_html = admin.request('enrollment-review.php', id=enrollment)
check(code == 200 and 'value="approve"' in review_html and 'value="remove"' in review_html, 'review screen exposes explicit approval and removal forms')
_, text = admin.post('approve', {'id':enrollment})
check('Enrollment approved' in text, 'registrar approval')
_, text = student.post('withdraw', {'id':enrollment})
check('Only your pending request' in text, 'approved request cannot be withdrawn')
code, text = student.request('enrollment', id=enrollment)
check(code == 200 and 'Stored total units' in text, 'printable own enrollment')
_, text = admin.post('approve', {'id':2})
check('Duplicate legacy enrollments' in text, 'legacy duplicates block unsafe approval')
_, text = admin.post('approve', {'id':1})
check('missing or ambiguous student' in text, 'orphan enrollment blocks approval')
_, text = admin.post('delete', dict(resource='students', id=profile))
check('Enrollment history exists' in text, 'student history protected')
_, text = admin.post('delete', dict(resource='accounts', id=account))
check('linked student profile' in text, 'linked account deletion blocked')
_, text = student.post('password', dict(current_password='wrong', password='Newbar123', confirmation='Newbar123'))
check('current password is incorrect' in text, 'password change verifies current credential')
_, text = student.post('password', dict(current_password='Testbar123', password='Newbar123', confirmation='Newbar123'))
check('Password updated' in text, 'student changes own password')
student.post('logout', {})
check('Hello,' in student.login('wbtest1','Newbar123')[1], 'updated credential works')
check('Sign in to your campus' in student.post('logout', {})[1], 'logout returns to sign-in')
check(student.request('profile')[1].find('Sign in to your campus') >= 0, 'logout removes access')
for resource,id in [('schedules',schedule),('sections',section)]:
    check('Record deleted.' in admin.post('delete', dict(resource=resource,id=id))[1], resource + ' delete')
# Complete CRUD for unreferenced records, plus guards on referenced rows.
account2 = admin.save('accounts', dict(username='wbtest2', password='Testbar123', fullname='Disposable Student', user_type='student'))
admin.save('accounts', dict(username='wbtest2', password='', fullname='Updated Student', user_type='student'), account2)
profile2_fields = dict(accountID=str(account2), studnum='WB-0002', fname='Temporary', minitial='', lname='Student', course='temporary', yearlvl='1')
profile2 = admin.save('students', profile2_fields)
admin.save('students', profile2_fields | {'fname':'Updated'}, profile2)
program2 = admin.save('courses', dict(course_title='temporary', course_desc='Temporary program'))
admin.save('courses', dict(course_title='temporary', course_desc='Updated program'), program2)
subject2_values = subject_values | {'subjectcode':'WB102', 'course_con':'temporary'}
subject2 = admin.save('subjects', subject2_values)
admin.save('subjects', subject2_values | {'subjecttitle':'Updated subject'}, subject2)
section2_values = dict(section_name='TMP-A', section_course='temporary', yr_level='1', min_num='1', max_num='40')
section2 = admin.save('sections', section2_values)
admin.save('sections', section2_values | {'max_num':'45'}, section2)
check('program is referenced' in admin.post('delete', dict(resource='courses',id=program2))[1], 'referenced program deletion blocked')
for resource,id in [('students',profile2),('accounts',account2),('subjects',subject2),('sections',section2),('courses',program2)]:
    check('Record deleted.' in admin.post('delete', dict(resource=resource,id=id))[1], resource + ' unreferenced delete')
check('own account' in admin.post('delete', dict(resource='accounts', id=2))[1], 'admin self-delete blocked')
# Legacy cleanup is explicit, through registrar forms, never an automatic migration.
check(student.request('edit-enrollment', id=2)[1].find('Sign in to your campus') >= 0, 'signed-out correction page denied')
check(legacy.login('dennis','123456')[0] == 200, 'legacy student account remains usable')
check(legacy.request('edit-enrollment', id=2)[0] == 403, 'student correction page denied')
for legacy_id in [3,4,5,6,7,8]:
    check('Pending enrollment removed.' in admin.post('remove-pending', dict(id=legacy_id))[1], f'registrar removes reviewed duplicate #{legacy_id}')
_, text = admin.post('revise', {'id':2, 'profile_id':2, 'subjects[]':['1','3']})
check('Pending enrollment corrected' in text, 'registrar corrects legacy subjects and units through form')
check('Enrollment approved' in admin.post('approve', {'id':2})[1], 'corrected legacy enrollment can be approved')
check('Only pending requests' in admin.post('remove-pending', {'id':2})[1], 'approved history cannot be removed')
print(f'{checks} workflow checks passed. Approved fixtures are retained in this DISPOSABLE database.')
