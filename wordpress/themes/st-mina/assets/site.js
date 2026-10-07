// السلوك المشترك لكل صفحات الموقع: القائمة، والشريط العائم، والجدول، والمرشحات، والتبويبات،
// وسلايدر الأقوال، وعارض الصور، والعد التنازلي، وذرات الغبار، وحركات الظهور.
// ما يخص صفحة واحدة يبقى في <script> داخل الصفحة نفسها، ويستخدم window.Site.
(() => {
  const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const root = document.documentElement;
  const reduced = false; // الحركة تعمل للكل بقرار المستخدم
  const animate = root.classList.contains('anim') && window.gsap && window.ScrollTrigger;
  if (!animate) root.classList.remove('anim');
  const frame = $('.hero, .page-hero');
  window.Site = { $, $$, animate, frame };

  // ---------- القوائم المنسدلة: صفحات كل قسم، ودخول الخدام لكل اجتماع ----------
  // الروابط بتتحسب من رابط القسم نفسه، فتشتغل في كل الصفحات (حتى 404 اللي روابطها من جذر الموقع)
  const SUB = {
    'church-history': [['النشأة', 'church-history.html'], ['الآباء', 'church-fathers.html'], ['الصور', 'church-gallery.html'], ['الموقع', 'church-location.html']],
    'worship': [['مواعيد القداسات', 'worship.html#schedule'], ['الاجتماعات الثابتة', 'worship.html#meetings'], ['القراءات اليومية', 'worship.html#readings'], ['الاعتراف', 'worship.html#confession']],
    'services': [['مدارس الأحد', 'services.html#g-sunday'], ['الاجتماعات', 'services.html#g-meet'], ['الخدام', 'services.html#g-servants'], ['الفرق والأنشطة', 'services.html#g-teams'], ['الحضانة', 'services.html#g-nursery'], ['الأسرة والمجتمع', 'services.html#g-family']],
    'library': [['العظات', 'library.html#panel-sermons'], ['النشرات', 'library.html#panel-bulletins'], ['الترانيم', 'library.html#panel-hymns']],
    'news': [['الأخبار والإعلانات', 'news.html'], ['المناسبة القادمة', 'season.html']]
  };
  // لإضافة نظام حضور لاجتماع تاني: سطر جديد هنا بالاسم ورابط شاشة الدخول بتاعته
  const MEETINGS = [['اجتماع إعداد الخدام', 'attend-login.html']];

  // في WordPress الروابط شكلها /worship/ مش worship.html، فالـ theme بيبعت SITE_BASE ونحوّل الروابط ليه
  const BASE = window.SITE_BASE;
  if (BASE) {
    const toWp = h => h.replace(/^([a-z0-9-]+)\.html/, (_, p) => BASE + p + '/');
    for (const k in SUB) SUB[k] = SUB[k].map(([t, h]) => [t, toWp(h)]);
    MEETINGS.forEach(m => { m[1] = toWp(m[1]); });
  }
  const chev = '<svg class="icon" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>';
  // اسم القسم من آخر جزء في الرابط: worship.html أو /worship/
  const keyOf = a => { try { return new URL(a.href).pathname.split('/').filter(Boolean).pop().replace(/\.html$/, ''); } catch { return ''; } };
  // الخادم ممكن يعرض الصفحة من غير .html، فالمقارنة بعد شيلها
  const bare = u => u.split('#')[0].replace(/\.html$/, '');
  const here = bare(location.href);
  const subLinks = (items, base) => items.map(([t, h]) => {
    const url = new URL(h, base).href;
    return `<li><a href="${url}"${!url.includes('#') && bare(url) === here ? ' aria-current="page"' : ''}>${t}</a></li>`;
  }).join('');
  let ddN = 0;
  const setOpen = (box, open) => { box.classList.toggle('open', open); $('.dd-btn', box).setAttribute('aria-expanded', open); };
  const closeAll = except => $$('.has-dd.open').forEach(b => { if (b !== except) setOpen(b, false); });

  $$('.top-nav li, .float-nav nav li, #drawer li').forEach(li => {
    const a = $('a', li), items = a && SUB[keyOf(a)];
    if (!items) return;
    const id = 'dd' + ++ddN;
    li.classList.add('has-dd');
    a.insertAdjacentHTML('afterend', `<button class="dd-btn" type="button" aria-expanded="false" aria-controls="${id}" aria-label="صفحات ${a.textContent.trim()}">${chev}</button><ul class="dd" id="${id}">${subLinks(items, a.href)}</ul>`);
  });

  // زر دخول الخدام: أيقونة فوق في الهيدر، وتحتها الاجتماعات اللي ليها نظام حضور
  const navBase = ($('.top-nav a[href], #drawer li a[href]') || {}).href || location.href;
  const loginList = MEETINGS.map(([t, h]) => `<li><a href="${new URL(h, navBase).href}">${t}</a></li>`).join('');
  const userIcon = '<svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="8.5" r="3.5"/><path d="M5.5 19.5a6.5 6.5 0 0 1 13 0"/></svg>';
  $$('.hero-top .menu-btn, .float-nav .menu-btn').forEach(btn => {
    const id = 'dd' + ++ddN;
    btn.insertAdjacentHTML('beforebegin', `<div class="has-dd login-dd"><button class="dd-btn login-btn" type="button" aria-expanded="false" aria-controls="${id}" aria-label="دخول الخدام">${userIcon}</button><div class="dd" id="${id}"><p class="dd-title">دخول الخدام</p><ul>${loginList}</ul></div></div>`);
  });
  const drawerList = $('#drawer > ul');
  if (drawerList) drawerList.insertAdjacentHTML('afterend', `<div class="drawer-login"><p>${userIcon}دخول الخدام</p><ul>${loginList}</ul></div>`);

  $$('.has-dd').forEach(box => {
    $('.dd-btn', box).addEventListener('click', e => { e.stopPropagation(); const open = !box.classList.contains('open'); closeAll(box); setOpen(box, open); });
    box.addEventListener('focusout', e => { if (!box.contains(e.relatedTarget) && !box.closest('#drawer')) setOpen(box, false); });
  });
  document.addEventListener('click', e => { if (!e.target.closest('#drawer')) closeAll(); });
  addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    const open = $('.has-dd.open:not(#drawer .has-dd)');
    if (open) { setOpen(open, false); $('.dd-btn', open).focus(); }
  });

  // ---------- القائمة ----------
  const drawer = $('#drawer');
  if (drawer) {
    const setMenu = open => { drawer.classList.toggle('open', open); document.body.style.overflow = open ? 'hidden' : ''; if (open) $('#menuClose').focus(); };
    $$('[data-open-menu]').forEach(b => b.addEventListener('click', () => setMenu(true)));
    $('#menuClose').addEventListener('click', () => setMenu(false));
    $$('#drawer a').forEach(a => a.addEventListener('click', () => setMenu(false)));
    addEventListener('keydown', e => { if (e.key === 'Escape' && drawer.classList.contains('open')) setMenu(false); });
  }

  // ---------- الشريط العائم ----------
  const floatNav = $('#floatNav');
  if (floatNav && frame) {
    const onScroll = () => floatNav.classList.toggle('show', scrollY > frame.offsetHeight * .7);
    addEventListener('scroll', onScroll, { passive: true }); onScroll();
  }

  // ---------- يوم اليوم في جداول المواعيد ----------
  $$(`.day[data-day="${new Date().getDay()}"]`).forEach(d => { d.classList.add('today'); $('.day-n', d).insertAdjacentHTML('beforeend', '<small>اليوم</small>'); });

  // ---------- المرشحات ----------
  const filter = (btns, attr, items, k) => btns.forEach(b => b.addEventListener('click', () => {
    btns.forEach(x => x.setAttribute('aria-pressed', x.dataset[attr] === b.dataset[attr]));
    items.forEach(it => {
      const ok = b.dataset[attr] === 'all' || it.dataset[k] === b.dataset[attr];
      it.classList.toggle('hide', !ok);
      if (ok && animate) gsap.fromTo(it, { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: .6, ease: 'power3.out', overwrite: true });
    });
    if (animate) ScrollTrigger.refresh();
  }));
  filter($$('[data-f]'), 'f', $$('.svc'), 'g');
  filter($$('.tabs .chip[data-n]'), 'n', $$('.news, .nbub'), 't');
  filter($$('.chip[data-gf]'), 'gf', $$('.g[data-c]'), 'c');

  // ---------- التبويبات (role=tab يتحكم في role=tabpanel) ----------
  $$('[role=tablist]:not(.qs-dots)').forEach(list => {
    const tabs = $$('[role=tab]', list);
    const select = t => {
      tabs.forEach(x => { const on = x === t; x.setAttribute('aria-selected', on); x.setAttribute('aria-pressed', on); x.tabIndex = on ? 0 : -1; $('#' + x.getAttribute('aria-controls')).hidden = !on; });
      const panel = $('#' + t.getAttribute('aria-controls'));
      if (animate) { gsap.fromTo(panel, { opacity: 0, y: 16 }, { opacity: 1, y: 0, duration: .6, ease: 'power3.out' }); ScrollTrigger.refresh(); }
    };
    // فتح التبويب من الرابط: #اسم-اللوحة
    const fromHash = () => tabs.find(t => location.hash && '#' + t.getAttribute('aria-controls') === location.hash);
    if (fromHash()) select(fromHash());
    // من القائمة المنسدلة وإنت في نفس الصفحة: الرابط بيغيّر الـ hash بس
    addEventListener('hashchange', () => { const t = fromHash(); if (t) select(t); });
    tabs.forEach((t, i) => {
      t.addEventListener('click', () => select(t));
      t.addEventListener('keydown', e => {
        const step = e.key === 'ArrowLeft' ? 1 : e.key === 'ArrowRight' ? -1 : 0;
        if (step) { const n = tabs[(i + step + tabs.length) % tabs.length]; n.focus(); select(n); }
      });
    });
  });

  // ---------- شريط الأقوال ----------
  const slider = $('#qslider');
  if (slider) {
    const slides = $$('.qs'), dots = $('.qs-dots'); let cur = 0, timer;
    slides.forEach((sl, i) => { const d = document.createElement('button'); d.setAttribute('role', 'tab'); d.setAttribute('aria-label', 'القول ' + (i + 1)); d.addEventListener('click', () => go(i)); dots.append(d); });
    const place = () => {
      const gap = Math.min(330, innerWidth * .36);
      slides.forEach((sl, i) => {
        let rel = i - cur; if (rel > slides.length / 2) rel -= slides.length; if (rel < -slides.length / 2) rel += slides.length;
        const a = Math.abs(rel);
        sl.style.transform = `translateX(calc(-50% + ${-rel * gap}px)) scale(${a === 0 ? 1 : a === 1 ? .78 : .6})`;
        sl.style.opacity = a === 0 ? 1 : a === 1 ? .45 : 0;
        sl.style.filter = a === 0 ? 'none' : 'brightness(.6) blur(1px)';
        sl.style.zIndex = 3 - a;
        sl.classList.toggle('is-active', a === 0); sl.classList.toggle('is-vis', a <= 1);
        sl.tabIndex = a === 0 ? 0 : -1;
      });
      [...dots.children].forEach((d, i) => d.setAttribute('aria-selected', i === cur));
    };
    const go = i => { cur = (i + slides.length) % slides.length; place(); };
    const auto = () => { clearInterval(timer); if (!reduced) timer = setInterval(() => go(cur + 1), 5000); };
    slides.forEach((sl, i) => sl.addEventListener('click', e => { if (i !== cur) { e.stopImmediatePropagation(); go(i); auto(); } }, true));
    $$('.rail-ctl button').forEach(b => b.addEventListener('click', () => { go(cur + (b.dataset.dir === 'next' ? 1 : -1)); auto(); }));
    let x0 = null;
    slider.addEventListener('pointerdown', e => { x0 = e.clientX; });
    slider.addEventListener('pointerup', e => { if (x0 === null) return; const dx = e.clientX - x0; x0 = null; if (Math.abs(dx) > 40) { go(cur + (dx > 0 ? 1 : -1)); auto(); } });
    slider.addEventListener('keydown', e => { if (e.key === 'ArrowLeft') { go(cur + 1); auto(); } if (e.key === 'ArrowRight') { go(cur - 1); auto(); } });
    slider.addEventListener('mouseenter', () => clearInterval(timer));
    slider.addEventListener('mouseleave', auto);
    slider.addEventListener('focusin', () => clearInterval(timer));
    addEventListener('resize', place);
    place(); auto();
  }

  // ---------- عارض الصور ----------
  const lb = $('#lightbox');
  if (lb) {
    const lbImg = $('#lbImg'), lbCap = $('#lbCap'), all = $$('[data-lb]'); let items = all, idx = 0;
    const show = i => { idx = (i + items.length) % items.length; const im = $('img', items[idx]); lbImg.src = im.src; lbImg.alt = im.alt; lbCap.textContent = items[idx].dataset.cap || ''; };
    // التنقل بين الصور الظاهرة فقط (بعد الفلترة)
    all.forEach(it => it.addEventListener('click', () => { if (it.classList.contains('qs') && !it.classList.contains('is-active')) return; items = all.filter(x => !x.classList.contains('hide')); show(items.indexOf(it)); lb.showModal(); }));
    $('#lbClose').addEventListener('click', () => lb.close());
    $('#lbPrev').addEventListener('click', () => show(idx - 1));
    $('#lbNext').addEventListener('click', () => show(idx + 1));
    lb.addEventListener('click', e => { if (e.target === lb) lb.close(); });
    lb.addEventListener('keydown', e => { if (e.key === 'ArrowLeft') show(idx + 1); if (e.key === 'ArrowRight') show(idx - 1); });
  }

  // ---------- مشغّل يوتيوب بأجزاء: صورة الفيديو أولًا، ويُحمَّل يوتيوب عند الضغط فقط ----------
  $$('[data-yt]').forEach(box => {
    const frame = $('.yt-frame', box), parts = $$('.yt-part', box);
    let cur = parts.find(p => p.getAttribute('aria-pressed') === 'true') || parts[0];
    const embed = () => {
      const old = $('iframe', frame); if (old) old.remove();
      const f = document.createElement('iframe');
      f.src = `https://www.youtube-nocookie.com/embed/${cur.dataset.id}?autoplay=1&rel=0`;
      f.title = cur.dataset.title; f.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen'; f.allowFullscreen = true;
      frame.append(f);
      dispatchEvent(new Event('site:media'));
    };
    const show = p => {
      cur = p; parts.forEach(x => x.setAttribute('aria-pressed', x === p));
      $('img', frame).src = p.dataset.thumb;
      $('.yt-cap', frame).textContent = `${$('b', p).textContent} · ${$('small', p).textContent}`;
      $('.yt-play', frame).setAttribute('aria-label', 'تشغيل ' + $('b', p).textContent);
      if ($('iframe', frame)) embed();
    };
    $('.yt-play', frame).addEventListener('click', embed);
    parts.forEach(p => p.addEventListener('click', () => show(p)));
  });

  // ---------- ترنيمة الخلفية: مشغّل ساوند كلاود مخفي يتحكم فيه زر الشمعة العائم ----------
  // التشغيل افتراضي، لكن المتصفح لا يسمح بالصوت قبل ضغطة، فتبدأ مع أول لمسة في الصفحة. رقم الترنيمة وموضعها يُحفظان
  // في localStorage لتكمل من نفس الثانية في الصفحة التالية. القائمة تنتقل وحدها، وبعد آخر ترنيمة تعود لأولها.
  (() => {
    // لتغيير الترانيم: رابط قائمة تشغيل على ساوند كلاود (أو رابط ترنيمة واحدة)
    const TRACK = 'https://soundcloud.com/abanoub-s-morise/sets/church-st-mina', KEY = 'hymn';
    // الحالة المحفوظة تخص هذا الرابط فقط، فتغييره يبدأ من أول ترنيمة
    const read = () => { try { const s = JSON.parse(localStorage.getItem(KEY)) || {}; return s.track === TRACK ? s : {}; } catch { return {}; } };
    const save = s => { try { localStorage.setItem(KEY, JSON.stringify({ ...read(), ...s, track: TRACK })); } catch {} };
    const btn = document.createElement('button');
    btn.type = 'button'; btn.className = 'hymn'; btn.dataset.state = 'off';
    btn.innerHTML = '<span class="hymn-candle" aria-hidden="true"></span><span class="hymn-tip" aria-hidden="true">اضغط لتكمل الترنيمة</span>';
    document.body.append(btn);
    let widget = null, ready = null, playing = false, idx = read().idx || 0, pos = read().pos || 0, seek = null, lastSave = 0, title = 'الترنيمة';
    const label = () => btn.setAttribute('aria-label', (playing ? 'إيقاف ' : 'تشغيل ') + title);
    const set = st => { btn.dataset.state = st; playing = st === 'on'; btn.setAttribute('aria-pressed', playing); label(); };
    label(); btn.setAttribute('aria-pressed', 'false');

    // يُحمَّل ساوند كلاود عند أول حاجة فقط
    const load = () => ready || (ready = new Promise(res => {
      const f = document.createElement('iframe');
      f.className = 'hymn-frame'; f.title = 'مشغّل الترانيم'; f.allow = 'autoplay'; f.tabIndex = -1; f.setAttribute('aria-hidden', 'true');
      f.src = 'https://w.soundcloud.com/player/?url=' + encodeURIComponent(TRACK) + '&auto_play=false&visual=false&show_artwork=false&buying=false&sharing=false&download=false';
      // السكربت أولًا ثم الإطار، حتى لا تفوتنا إشارة READY
      const s = document.createElement('script'); s.src = 'https://w.soundcloud.com/player/api.js';
      s.onload = () => {
        document.body.append(f);
        const E = SC.Widget.Events; widget = SC.Widget(f);
        widget.bind(E.READY, () => res(widget));
        widget.bind(E.PLAY, () => {
          set('on');
          widget.getCurrentSoundIndex(i => {
            if (i !== idx) { idx = i; pos = 0; }
            save({ playing: true, idx, pos });
            if (seek) { widget.seekTo(seek); seek = null; }
          });
          widget.getCurrentSound(snd => { if (snd && snd.title) { title = 'ترنيمة: ' + snd.title; btn.title = snd.title; label(); } });
        });
        widget.bind(E.PAUSE, () => { if (btn.dataset.state === 'on') set('off'); });
        // بعد آخر ترنيمة نعود لأول القائمة (وفي الترنيمة الواحدة تعيد نفسها)
        widget.bind(E.FINISH, () => widget.getSounds(list => widget.getCurrentSoundIndex(i => { if (i >= list.length - 1) { idx = 0; pos = 0; widget.skip(0); } })));
        widget.bind(E.PLAY_PROGRESS, e => { pos = e.currentPosition; if (Date.now() - lastSave > 2000) { lastSave = Date.now(); save({ idx, pos }); } });
      };
      document.head.append(s);
    }));

    // نرجع لنفس الترنيمة، والقفز للثانية المحفوظة يحصل أول ما تبدأ
    const play = () => load().then(w => { seek = pos || null; if (idx > 0) w.skip(idx); else w.play(); });
    // byUser: الزائر قفلها بنفسه، فتفضل مقفولة في كل الصفحات لحد ما يشغّلها تاني
    const stop = (byUser = false) => { if (widget) widget.pause(); set('off'); save({ playing: false, pos, ...(byUser && { off: true }) }); };
    const start = () => { disarm(); save({ off: false }); set('loading'); play(); setTimeout(() => { if (btn.dataset.state === 'loading') set('off'); }, 8000); };
    btn.addEventListener('click', () => playing ? stop(true) : start());

    // التشغيل افتراضي: نحاول نبدأ مع فتح الصفحة، ولو المتصفح منع الصوت (وده المعتاد قبل أي ضغطة)
    // تبدأ الترنيمة مع أول لمسة أو ضغطة في أي مكان في الصفحة
    const first = e => { if (!btn.contains(e.target)) start(); else disarm(); };
    const arm = () => ['pointerdown', 'keydown'].forEach(t => addEventListener(t, first, { capture: true, once: true }));
    function disarm() { ['pointerdown', 'keydown'].forEach(t => removeEventListener(t, first, { capture: true })); }
    if (!read().off) {
      const wasPlaying = read().playing;
      set('loading'); play();
      setTimeout(() => { if (!playing) { set(wasPlaying ? 'resume' : 'off'); arm(); } }, 2500);
    }
    // صوت آخر في الصفحة (فيديو القداس أو مشغّل صوت) يوقف الترنيمة
    addEventListener('site:media', () => { disarm(); if (playing) stop(); });
    document.addEventListener('play', e => { if (!e.target.muted && btn.dataset.state !== 'off') stop(); }, true);
    addEventListener('pagehide', () => { if (playing) save({ pos }); });
  })();

  // ---------- العد التنازلي ----------
  const cd = $('#countdown');
  if (cd) {
    const target = new Date(cd.dataset.date).getTime(), pad = n => String(n).padStart(2, '0');
    const tick = () => { const s = Math.max(0, Math.floor((target - Date.now()) / 1000)); const v = { d: Math.floor(s / 86400), h: Math.floor(s % 86400 / 3600), m: Math.floor(s % 3600 / 60), s: s % 60 }; for (const k in v) $(`[data-u="${k}"]`, cd).textContent = pad(v[k]); };
    tick(); setInterval(tick, 1000);
  }

  // ---------- ذرات الغبار في ضوء الواجهة ----------
  const cv = $('#dust');
  if (cv && frame && !reduced) {
    const ctx = cv.getContext('2d'); let W, H, motes = [], running = true;
    const size = () => { const r = frame.getBoundingClientRect(), dpr = Math.min(devicePixelRatio || 1, 2); W = r.width; H = r.height; cv.width = W * dpr; cv.height = H * dpr; cv.style.width = W + 'px'; cv.style.height = H + 'px'; ctx.setTransform(dpr, 0, 0, dpr, 0, 0); };
    const seed = () => { const n = (W < 700 ? 40 : 90) * (frame.classList.contains('page-hero') ? .6 : 1); motes = Array.from({ length: Math.round(n) }, () => ({ x: Math.random() * W * .65, y: Math.random() * H, r: Math.random() * 1.8 + .4, vx: Math.random() * .25 + .05, vy: -(Math.random() * .2 + .05), a: Math.random() * .6 + .2, p: Math.random() * Math.PI * 2 })); };
    const draw = () => {
      if (!running) return;
      ctx.clearRect(0, 0, W, H);
      for (const m of motes) {
        m.x += m.vx; m.y += m.vy; m.p += .02;
        if (m.y < -10 || m.x > W * .75) { m.x = Math.random() * W * .5; m.y = H + 10; }
        ctx.beginPath(); ctx.arc(m.x, m.y, m.r, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(255,224,170,${m.a * (.6 + .4 * Math.sin(m.p))})`; ctx.fill();
      }
      requestAnimationFrame(draw);
    };
    size(); seed(); draw();
    addEventListener('resize', () => { size(); seed(); });
    new IntersectionObserver(([e]) => { const was = running; running = e.isIntersecting; if (running && !was) draw(); }).observe(frame);
  }

  if (!animate) return;

  // ---------- الحركة ----------
  gsap.registerPlugin(ScrollTrigger);

  // تقسيم العناوين إلى كلمات (بدون تقسيم الحروف حتى لا ينكسر اتصال الحروف العربية)
  $$('[data-split]').forEach(el => {
    const walk = node => [...node.childNodes].forEach(n => {
      if (n.nodeType === 3) {
        const frag = document.createDocumentFragment();
        n.textContent.split(/(\s+)/).forEach(part => {
          if (!part) return;
          if (/^\s+$/.test(part)) { frag.append(part); return; }
          const w = document.createElement('span'); w.className = 'w';
          const inner = document.createElement('span'); inner.textContent = part; w.append(inner); frag.append(w);
        });
        n.replaceWith(frag);
      } else if (n.nodeType === 1) walk(n);
    });
    walk(el);
  });

  // الواجهة الداخلية: الضوء يدخل ثم يظهر العنوان (مقدمة الرئيسية في home.html)
  if (frame && frame.classList.contains('page-hero')) {
    gsap.timeline({ defaults: { ease: 'power3.out' } })
      .to('.page-hero .hero-bg img', { scale: 1, duration: 2.2, ease: 'power2.out' }, 0)
      .to('.page-hero .rays', { opacity: .75, duration: 1.8 }, .1)
      .to('.page-hero #dust', { opacity: 1, duration: 1.6 }, .4)
      .to('.page-hero [data-split] .w > span', { y: 0, duration: 1.1, stagger: .08 }, .3)
      .to('.page-hero [data-intro]', { opacity: 1, duration: 1, stagger: .06, startAt: { y: 20 }, y: 0 }, .5);
    gsap.to('.page-hero .hero-bg img', { yPercent: 8, ease: 'none', scrollTrigger: { trigger: '.page-hero', start: 'top top', end: 'bottom top', scrub: true } });
  }

  // العناوين: كلمة كلمة
  $$('[data-split]').forEach(el => { if (el.closest('.hero, .page-hero')) return;
    gsap.to($$('.w > span', el), { y: 0, duration: 1.1, stagger: .07, ease: 'power3.out', scrollTrigger: { trigger: el, start: 'top 86%' } });
  });

  // العناصر: صعود متدرج على دفعات
  ScrollTrigger.batch('[data-rise]', {
    start: 'top 90%',
    onEnter: batch => gsap.to(batch, { opacity: 1, y: 0, duration: 1, stagger: .08, ease: 'power3.out', overwrite: true })
  });

  // خلفيات الأقسام الخاصة: تظهر وتختفي بنعومة مع التمرير
  $$('.sec-bg img').forEach(img => {
    gsap.timeline({ scrollTrigger: { trigger: img.closest('section'), start: 'top bottom', end: 'bottom top', scrub: true } })
      .fromTo(img, { opacity: 0 }, { opacity: .9, duration: 1, ease: 'none' })
      .to(img, { opacity: .9, duration: 2, ease: 'none' })
      .to(img, { opacity: 0, duration: 1, ease: 'none' });
  });

  // الغلاف الثابت: واضح بعد الواجهة، ثم يهدأ ويتموه بالتدريج مع النزول
  const coverStart = $('[data-cover-start]');
  if (coverStart) gsap.fromTo('.site-cover .veil', { opacity: 0 }, { opacity: 1, ease: 'none', scrollTrigger: { trigger: coverStart, start: 'top 80%', end: 'top -60%', scrub: true } });

  // الدوائر: تظهر بتكبير هادئ متدرج
  ScrollTrigger.batch('[data-bub]', {
    start: 'top 92%',
    onEnter: batch => gsap.to(batch, { opacity: 1, scale: 1, duration: 1.2, stagger: .06, ease: 'back.out(1.4)', overwrite: true })
  });

  // الصور الكبيرة: كشف من الأسفل
  $$('[data-clip]').forEach(el => gsap.to(el, { clipPath: 'inset(0% 0 0 0)', duration: 1.6, ease: 'power4.inOut', scrollTrigger: { trigger: el, start: 'top 82%' } }));

  // تزحزح داخل الصور
  $$('[data-parallax]').forEach(img => gsap.fromTo(img, { yPercent: -6, scale: 1.12 }, { yPercent: 6, ease: 'none', scrollTrigger: { trigger: img.parentElement, start: 'top bottom', end: 'bottom top', scrub: true } }));
})();
