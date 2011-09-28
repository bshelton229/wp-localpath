<h2>LocalPath Settings</h2>

<?php if($localpath_notice): ?>
  <div class="updated"><p><?php echo $localpath_notice; ?></p></div>
<?php endif; ?>

<form action="" method="post">
  <input type="hidden" name="action" value="update" id="action" />
  <p><label for="hosts">Hostnames considered local:</label></p>
  <p><textarea name="hosts" id="hosts" rows="5" cols="40"><?php echo _localpath_opts(); ?></textarea></p>
  <p><input type="submit" value="Save options"></p>
</form>
