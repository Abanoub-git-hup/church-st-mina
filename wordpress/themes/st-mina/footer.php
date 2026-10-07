<?php
/**
 * نهاية كل صفحة: الـ footer، وعارض الصور، و wp_footer.
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<!-- ===== التذييل ===== -->
<footer class="footer">
  <div class="wrap">
    <div class="f-cta">
      <h2 data-split>عندك مكان معنا في <b>بيت الله</b></h2>
      <div data-rise><a class="pill gold" href="<?php stmina_link( 'services' ); ?>">اختر خدمتك <span class="dot"><svg class="icon"><use href="#i-left"/></svg></span></a></div>
    </div>
    <div class="f-grid">
      <div>
        <a class="brand" href="#top"><img src="<?php stmina_media( 'brand/logo.png' ); ?>" alt=""><span><b>كنيسة السيدة العذراء ومارمينا</b><span class="nw">والبابا كيرلس السادس</span> <span class="nw"><span class="sep">· </span>الجبل الأصفر</span></span></a>
        <p>مطرانية شبين القناطر وتوابعها.</p>
        <div class="socials">
          <a class="circle" href="https://www.facebook.com/santmarychurch" target="_blank" rel="noopener" aria-label="صفحة الكنيسة على فيسبوك"><svg class="icon"><use href="#i-facebook"/></svg></a>
          <a class="circle" href="https://www.youtube.com/@%D8%A7%D9%84%D9%82%D9%85%D8%B5%D9%83%D9%8A%D8%B1%D9%84%D8%B3%D8%B1%D9%88%D9%85%D8%A7%D9%86%D9%8A/" target="_blank" rel="noopener" aria-label="قناة يوتيوب"><svg class="icon"><use href="#i-youtube"/></svg></a>
          <a class="circle" href="https://wa.me/201227445837" target="_blank" rel="noopener" aria-label="واتساب الكنيسة"><svg class="icon"><use href="#i-whatsapp"/></svg></a>
        </div>
      </div>
      <div><h4>الموقع</h4><ul><li><a href="<?php stmina_link( 'church-history' ); ?>">الكنيسة</a></li><li><a href="<?php stmina_link( 'worship' ); ?>">العبادة</a></li><li><a href="<?php stmina_link( 'library' ); ?>">المكتبة</a></li><li><a href="<?php stmina_link( 'news' ); ?>">الأخبار</a></li><li><a href="<?php stmina_link( 'church-gallery' ); ?>">صور الكنيسة</a></li></ul></div>
      <div><h4>الخدمات</h4><ul><li><a href="<?php stmina_link( 'services', '#g-sunday' ); ?>">مدارس الأحد</a></li><li><a href="<?php stmina_link( 'services', '#g-meet' ); ?>">الاجتماعات</a></li><li><a href="<?php stmina_link( 'services', '#g-servants' ); ?>">الخدام</a></li><li><a href="<?php stmina_link( 'services', '#g-teams' ); ?>">الفرق والأنشطة</a></li><li><a href="<?php stmina_link( 'services', '#g-family' ); ?>">الأسرة والمجتمع</a></li></ul></div>
      <div><h4>تواصل معنا</h4><ul><li>عرب العيايدة، ناحية الجبل الأصفر</li><li><a href="tel:+201008025261" dir="ltr">010 0802 5261</a></li><li><a href="mailto:ak.romany7@gmail.com" dir="ltr">ak.romany7@gmail.com</a></li><li><a href="https://wa.me/201227445837" target="_blank" rel="noopener">واتساب: <span dir="ltr">0122 744 5837</span></a></li><li><a href="https://www.facebook.com/santmarychurch" target="_blank" rel="noopener">صفحة الكنيسة على فيسبوك</a></li></ul></div>
    </div>
    <div class="f-bar">
      <span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> كنيسة السيدة العذراء ومارمينا والبابا كيرلس السادس بالجبل الأصفر</span>
    </div>
  </div>
</footer>

<dialog class="lightbox" id="lightbox" aria-label="عارض الصور">
  <button class="lb-close" id="lbClose" aria-label="إغلاق"><svg class="icon"><use href="#i-x"/></svg></button>
  <button class="lb-btn lb-prev" id="lbPrev" aria-label="السابقة"><svg class="icon"><use href="#i-right"/></svg></button>
  <button class="lb-btn lb-next" id="lbNext" aria-label="التالية"><svg class="icon"><use href="#i-left"/></svg></button>
  <figure><img id="lbImg" alt=""><figcaption id="lbCap"></figcaption></figure>
</dialog>

<?php wp_footer(); ?>
</body>
</html>
