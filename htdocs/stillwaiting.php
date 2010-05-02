<? require('header.php'); ?>
<div class="bigmessage">
  Please hang on while we stalkify <?= $username ?>
  <br/>
  This might take a few long minutes
</div>
<script>
  window.setTimeout(function(){location.href=location.href;}, 10000);
</script>
<? require('footer.php'); ?>
