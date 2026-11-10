// خاص بالعبادة: تاريخ النهارده على دايرة القراءات
(() => {
  const { $ } = window.Site;
  const el = $("#readDate");
  if (el) el.textContent = new Intl.DateTimeFormat("ar-EG-u-nu-latn", { weekday: "long", day: "numeric", month: "long" }).format(new Date());
})();
