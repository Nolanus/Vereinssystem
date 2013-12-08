<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
      <title><?php if (isset($title)){
        echo htmlspecialchars($title)." - ";
      }
      if (!empty($settings['vereinsname'])){
        echo $settings['vereinsname']." - ";
      }
      echo "VereinsSystem";
      ?></title>
      <meta name="description" content="Vereinssystem, Informatik-Abitur 2012 Projekt" />
      <meta name="author" content="Nicolas Jourdan, Sebastian Fuss" />
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
      <link rel="stylesheet" type="text/css" href="css/main.css" />
      <link rel="icon" href="images/favicon.ico" type="image/x-icon" />
      <link rel="apple-touch-icon" href="images/apple-touch-icon.png" />
      <!--[if IE]>
      <style type="text/css">
          /* IE Hacks */
          body{
              text-align: center;
          }
          #main{
              text-align: left;
          }
          #footer{
              width: 980px;
          }
      </style>
      <![endif]-->
	  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
      <script type="text/javascript">
	    $(document).ready(function() {
	      <?php
          if (isset($appendjs)){
            echo $appendjs;
          }
          ?>
		});
      </script>
  </head>
  <body>
	<div id="main">
		<div id="header" class="textcenter">
            <a href="index.php"><img src="images/logo_head.png" title="VereinsSystem" alt="VereinsSystem" /></a>
		</div>
        <div id="navi">

        <?php
        if (isset($_SESSION['uid'])){
            // Haben wir einen angemeldeten Nutzer? Nur dann den Menu-Inhalt anzeigen
        ?>
            <ul>
                <li><a href="index.php" title="Startseite">Home</a></li>
                <li><a href="members.php" title="Mitgliederübersicht">Mitglieder</a></li>
                <li><a href="abteilungen.php" title="Abteilungen">Abteilungen</a></li>
                <li><a href="orte.php" title="Orte">Orte</a></li>
                <li><a href="veranstaltungen.php" title="Veranstaltungen">Veranstaltungen</a></li>
                <?php
                if ($user['rights'] >= 5){
                  echo "<li><a href=\"einstellungen.php\" title=\"Einstellungen\">Einstellungen</a></li>\n";
                }
                ?>
                <li class="logoutlink"><a href="logout.php" title="Abmelden und aktuelle Sitzung beenden">Logout <?php echo htmlspecialchars($user['vorname']." ".$user['nachname']);?></a></li>
            </ul>
        <?php
        }
        ?>
        </div>
        <noscript>
        <p class="message warning"><b>Bitte aktivieren Sie JavaScript</b>, um diese Seite uneingeschränkt nutzen zu können!</p>
        </noscript>