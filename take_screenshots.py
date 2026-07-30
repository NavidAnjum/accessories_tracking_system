"""
Take screenshots of every ATS page and save them to screenshots/
Run: python take_screenshots.py
"""
import os, time
from playwright.sync_api import sync_playwright

BASE   = 'http://localhost/ed_module'
EMAIL  = 'admin@ed.local'
PASS   = 'Ats@2026'
OUT    = r'e:\xampp\htdocs\ed_module\screenshots'
ORDER  = 'ORD-2026-0012'   # an existing order to load for document pages

os.makedirs(OUT, exist_ok=True)

PAGES = [
    ('01_login',       f'{BASE}/pages/login.php',            False),
    ('02_dashboard',   f'{BASE}/pages/dashboard.php',        False),
    ('03_customers',   f'{BASE}/pages/customer-profile.php', False),
    ('04_create_cust', f'{BASE}/pages/create-customer.php',  False),
    ('05_item_master', f'{BASE}/pages/item-master.php',      False),
    ('06_intake',      f'{BASE}/pages/marketing-intake.php', True),
    ('07_costing',     f'{BASE}/pages/costing-review.php',   True),
    ('08_pi',          f'{BASE}/pages/sales.php',            True),
    ('09_marketing',   f'{BASE}/pages/marketing.php',        True),
    ('10_lc',          f'{BASE}/pages/lc.php',               True),
    ('11_exchange',    f'{BASE}/pages/exchange.php',         True),
    ('12_commercial',  f'{BASE}/pages/commercial.php',       True),
    ('13_packing',     f'{BASE}/pages/packing.php',          True),
    ('14_delivery',    f'{BASE}/pages/delivery.php',         True),
    ('15_truck',       f'{BASE}/pages/truck.php',            True),
    ('16_origin',      f'{BASE}/pages/origin.php',           True),
    ('17_beneficiary', f'{BASE}/pages/beneficiary.php',      True),
    ('18_forwarding',  f'{BASE}/pages/forwarding.php',       True),
    ('19_challan',     f'{BASE}/pages/po-status.php',        True),
    ('20_users',       f'{BASE}/pages/users.php',            False),
]

def load_order(page):
    """Type the order ID and click Load Order, then wait for data."""
    inp = page.locator('#oidInput')
    if inp.count():
        inp.fill(ORDER)
        btn = page.locator('button:has-text("Load Order")')
        if btn.count():
            btn.click()
            page.wait_for_timeout(1800)

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    ctx     = browser.new_context(viewport={'width': 1400, 'height': 900})
    page    = ctx.new_page()

    # ── Login ──────────────────────────────────────────────────────────────
    print('Logging in...')
    page.goto(f'{BASE}/pages/login.php', wait_until='networkidle')
    page.fill('input[name="email"]',    EMAIL)
    page.fill('input[name="password"]', PASS)
    page.click('button[type="submit"]')
    page.wait_for_url('**/dashboard.php', timeout=8000)
    print('  Logged in OK')

    # ── Screenshot each page ───────────────────────────────────────────────
    for name, url, needs_order in PAGES:
        print(f'  Capturing {name} ...')
        try:
            page.goto(url, wait_until='networkidle', timeout=12000)
            page.wait_for_timeout(600)

            if needs_order:
                load_order(page)

            # Scroll to top so header is visible
            page.evaluate('window.scrollTo(0, 0)')
            page.wait_for_timeout(300)

            path = os.path.join(OUT, f'{name}.png')
            page.screenshot(path=path, full_page=True)
            print(f'    Saved {path}')
        except Exception as e:
            print(f'    ERROR on {name}: {e}')

    browser.close()

print('\nAll screenshots saved to:', OUT)
