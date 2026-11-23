<div class="login-shell">
    <section class="login-story">
        <a class="brand" href="<?= e(url('login')) ?>">
            <span class="brand-icon">
                B
                <span>↗</span>
            </span>
            <span>
                BAR College
                <small>BINARY APPLICATION RESEARCH</small>
            </span>
        </a>
        <div class="story-body">
            <span class="eyebrow light">YOUR NEXT CHAPTER STARTS HERE</span>
            <h1>
                Big ideas.
                <br />
                Brighter
                <br />
                <em>beginnings.</em>
            </h1>
            <p>Your campus, connected. A simpler space to manage your academic journey.</p>
            <div class="orbit-art" aria-hidden="true">
                <div class="orbit orbit-one"></div>
                <div class="orbit orbit-two"></div>
                <div class="orbit-core">B↗</div>
                <span class="orbit-star">✳</span>
                <span class="orbit-tag">LEARN. BUILD. BECOME.</span>
            </div>
        </div>
        <div class="story-footer">
            <span>A WinBAR project</span>
            <span>Built for what comes next ↗</span>
        </div>
    </section>
    <section class="login-form-section">
        <div class="login-form-wrap">
            <span class="eyebrow">THE CAMPUS PORTAL</span>
            <h2>Welcome back.</h2>
            <p class="muted">A good day to move forward. Sign in to continue.</p>
            <form method="post" action="<?= e(url('login')) ?>" class="stack-form">
                <?= csrf() ?>
                <label for="username">Username</label>
                <input
                    id="username"
                    name="username"
                    maxlength="11"
                    autocomplete="username"
                    required
                    placeholder="Enter your username"
                />
                <label for="password">Password</label>
                <div class="password-wrap">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        maxlength="11"
                        autocomplete="current-password"
                        required
                        placeholder="Enter your password"
                    />
                    <button
                        type="button"
                        class="show-password"
                        aria-controls="password"
                        aria-pressed="false"
                    >
                        Show
                    </button>
                </div>
                <button class="button wide" type="submit">
                    Sign in to your campus
                    <span aria-hidden="true">↗</span>
                </button>
            </form>
            <p class="login-help">
                Need an account or help signing in?
                <br />
                <strong>Contact your college registrar.</strong>
            </p>
            <div class="login-footnote">
                <span class="live-dot"></span>
                One campus. Connected possibilities.
            </div>
        </div>
    </section>
</div>
