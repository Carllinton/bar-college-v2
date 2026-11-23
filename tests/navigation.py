"""Read-only navigation check after http_workflows.py has created its disposable fixtures.
BAR_TEST_URL=http://127.0.0.1:8080/ python3 tests/navigation.py
Run against the disposable test installation only; uses the suite's fictional student account.
"""
import http.cookiejar, os, re, urllib.parse, urllib.request, urllib.error
from html.parser import HTMLParser
BASE = os.environ.get('BAR_TEST_URL', 'http://127.0.0.1:8080/').rstrip('/') + '/'
PREFIX = urllib.parse.urlsplit(BASE).path
checks = 0

def check(ok, message):
    global checks
    assert ok, message
    checks += 1

class Links(HTMLParser):
    def __init__(self):
        super().__init__(); self.links = []
    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        for key in ('href', 'src', 'action'):
            if key in attrs: self.links.append((tag, key, attrs[key], attrs.get('method', 'get').lower()))

class Browser:
    def __init__(self):
        self.client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
        self.checked = set()
    def request(self, url, values=None):
        try:
            response = self.client.open(url, None if values is None else urllib.parse.urlencode(values).encode())
        except urllib.error.HTTPError as response:
            return response.code, response.read().decode(), response.url
        return response.code, response.read().decode(), response.url
    def login(self, username, password):
        _, text, _ = self.request(BASE + 'index.php')
        token = re.search(r'name="csrf" value="([a-f0-9]+)"', text)[1]
        status, text, url = self.request(BASE + 'index.php', dict(csrf=token, username=username, password=password))
        check(status == 200 and ('Your campus at a glance' in text or 'Hello,' in text), 'login ' + username)
        return url
    def inspect(self, url):
        status, text, final_url = self.request(url)
        check(status == 200, 'page loads ' + url)
        check(not re.search(r'(?:Fatal error|BAR College v2 incident)', text), 'page has no runtime error')
        parser = Links(); parser.feed(text)
        for tag, key, link, method in parser.links:
            if link.startswith('#'): continue
            target = urllib.parse.urljoin(final_url, link)
            parts = urllib.parse.urlsplit(target)
            check(parts.netloc == urllib.parse.urlsplit(BASE).netloc and parts.path.startswith(PREFIX), 'link remains in project: ' + target)
            check('index.php?page=' not in target, 'no obsolete router link')
            if key == 'action' and method == 'post': continue
            if target not in self.checked:
                code, _, _ = self.request(target)
                check(code == 200, 'linked page or asset loads: ' + target)
                self.checked.add(target)

admin = Browser(); admin.inspect(admin.login('admins', 'admins'))
for path in ['Admin/students.php', 'Admin/student-create.php', 'Admin/student-edit.php?id=2',
             'Admin/subjects.php', 'Admin/enrollment-requests.php', 'Admin/enrollment-review.php?id=2',
             'pages/enrollment-record.php?id=2', 'pages/account-settings.php']:
    admin.inspect(BASE + path)
student = Browser(); student.inspect(student.login('wbtest1', 'Newbar123'))
for path in ['student/my-profile.php','student/enroll.php','student/my-enrollments.php','pages/account-settings.php']:
    student.inspect(BASE + path)
for path in ['conn.php','original.sql','app/helpers.php','config/app.example.php','docs/README.md']:
    check(student.request(BASE + path)[0] == 403, 'protected path ' + path)
print(f'{checks} navigation/link checks passed for {BASE}')
