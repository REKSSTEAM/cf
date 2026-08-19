/**
 * AFVeo CF Solver — Node.js + Puppeteer
 * يحل Cloudflare تلقائياً ويرجع cookies + HTML
 *
 * POST /solve
 * Body: { "url": "https://..." }
 * Response: { "ok": true, "cookies": "...", "html": "...", "user_agent": "..." }
 */

const express   = require('express');
const puppeteer = require('puppeteer');

const app  = express();
const PORT = process.env.PORT || 3000;

app.use(express.json());
app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*');
  res.header('Access-Control-Allow-Headers', 'Content-Type');
  next();
});

// ── Browser instance (نفس الـ instance لكل الطلبات) ──────────────────────────
let browser = null;
async function getBrowser() {
  if (!browser) {
    browser = await puppeteer.launch({
      headless: 'new',
      args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-gpu',
        '--no-first-run',
        '--no-zygote',
        '--single-process',
      ],
    });
  }
  return browser;
}

// ── الدالة الرئيسية: حل CF ───────────────────────────────────────────────────
async function solveCF(targetUrl) {
  const br   = await getBrowser();
  const page = await br.newPage();

  try {
    // User Agent حقيقي
    await page.setUserAgent(
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
    );

    // انتظر تحميل الصفحة كاملاً
    await page.goto(targetUrl, {
      waitUntil: 'networkidle2',
      timeout: 30000,
    });

    // انتظر حل CF challenge (إذا موجود)
    await new Promise(r => setTimeout(r, 4000));

    // تحقق إذا لازال في CF challenge
    const title = await page.title();
    if (title.includes('Just a moment') || title.includes('Attention Required')) {
      // انتظر أكثر
      await new Promise(r => setTimeout(r, 6000));
    }

    // جلب HTML
    const html = await page.content();

    // جلب cookies
    const cookies     = await page.cookies();
    const cookieStr   = cookies.map(c => `${c.name}=${c.value}`).join('; ');
    const userAgent   = await page.evaluate(() => navigator.userAgent);
    const finalUrl    = page.url();

    await page.close();

    return {
      ok:         true,
      html:       html,
      cookies:    cookieStr,
      cookies_arr:cookies,
      user_agent: userAgent,
      final_url:  finalUrl,
    };

  } catch (err) {
    await page.close().catch(() => {});
    throw err;
  }
}

// ── Routes ────────────────────────────────────────────────────────────────────

// Health check
app.get('/', (req, res) => {
  res.json({ ok: true, service: 'AFVeo CF Solver', status: 'running' });
});

// الحل الرئيسي
app.post('/solve', async (req, res) => {
  const url = req.body?.url || req.query?.url;
  if (!url) return res.status(400).json({ ok: false, error: 'url مطلوب' });

  try {
    const result = await solveCF(url);
    res.json(result);
  } catch (err) {
    res.status(500).json({ ok: false, error: err.message });
  }
});

// GET للاختبار السريع
app.get('/solve', async (req, res) => {
  const url = req.query?.url;
  if (!url) return res.status(400).json({ ok: false, error: 'url مطلوب' });

  try {
    const result = await solveCF(url);
    // رجّع فقط المعلومات المهمة بدون HTML الكامل
    res.json({
      ok:        result.ok,
      cookies:   result.cookies,
      user_agent:result.user_agent,
      final_url: result.final_url,
      html_size: result.html.length,
      has_luluvdo: result.html.includes('luluvdo'),
      luluvdo_links: (result.html.match(/luluvdo\.com\/[a-zA-Z0-9]+/g) || []).slice(0,3),
    });
  } catch (err) {
    res.status(500).json({ ok: false, error: err.message });
  }
});

// ── Start ─────────────────────────────────────────────────────────────────────
app.listen(PORT, () => {
  console.log(`CF Solver running on port ${PORT}`);
  // تهيئة browser مسبقاً
  getBrowser().then(() => console.log('Browser ready')).catch(console.error);
});

// إعادة تشغيل browser إذا تعطّل
process.on('unhandledRejection', async (err) => {
  console.error('Unhandled rejection:', err);
  if (browser) {
    await browser.close().catch(() => {});
    browser = null;
  }
});
