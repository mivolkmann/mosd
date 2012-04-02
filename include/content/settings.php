<div class="pvorlage1">
   <span class="pheading">Client settings <?php print $this->_getExists('editmode') ? '<a href="'.HTTP_HOST.'/settings">→ switch to viewmode</a>' : '<a href="'.HTTP_HOST.'/settings/editmode">→ switch to editmode</a>' ?></span>
   <fieldset class="hl">
      <legend>account data</legend>
      <label>company name:</label>
      <?php print DB::result('name') ?>
      <br />
      <label>contact email:</label>
      <?php print DB::result('email') ?>
      <br />
      <label>login name:</label>
      <?php print DB::result('login') ?>
      <br />
   </fieldset>
</div>