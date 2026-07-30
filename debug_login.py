import os
os.makedirs('screenshots', exist_ok=True)
from playwright.sync_api import sync_playwright
with sync_playwright() as p:
    b = p.chromium.launch(headless=True)
    pg = b.new_page()
    pg.goto('http://localhost/ed_module/pages/login.php', wait_until='networkidle')
    pg.fill('input[name="email"]', 'admin@ed.local')
    pg.fill('input[name="password"]', 'Ats@2026')
    pg.click('button[type="submit"]')
    pg.wait_for_timeout(3000)
    print('URL after submit:', pg.url)
    pg.screenshot(path='screenshots/debug_after.png', full_page=True)
    b.close()
print('done')
