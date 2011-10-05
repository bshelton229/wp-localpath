<h2>LocalPath Settings</h2>
<?php if (!_localpath_enabled()): ?>

  <h3 style="color: red;">Localpath has been disabled in wp-config.php</h3>

<?php else: ?>

  <?php if($localpath_notice): ?>
    <div class="updated"><p><?php echo $localpath_notice; ?></p></div>
  <?php endif; ?>

  <?php if (!defined('LOCALPATH_HOSTS')): ?>
  <form action="" method="post">
    <input type="hidden" name="action" value="update" id="action" />
    <p><label for="hosts">Hostnames considered local:</label></p>
    <p><textarea name="hosts" id="hosts" rows="5" cols="40"><?php echo _localpath_opts(); ?></textarea></p>
    <p><input type="submit" value="Save options"></p>
  </form>
  <?php else: ?>
  <h3>You have defined your hosts in your wp-config.php</h3>
  <p style="background-color: #dedfe0; width: 50%; height: 50px; padding: 20px;"><?php echo _localpath_opts(); ?></p>
  <?php endif; ?>

<?php endif; ?>