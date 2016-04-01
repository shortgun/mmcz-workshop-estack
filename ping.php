<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Server Test Page</title>
</head>
<body>
<p>
OK
</p>
<p>
<b>Time and Date: </b> <?php date_default_timezone_set('America/Chicago'); echo date('D, d M Y H:i:s T'); ?>
</p>
<p>
<b>Host: </b> <?php echo php_uname("n"); ?>
</p>
</body>
</html>