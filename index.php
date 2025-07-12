<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>

<body style="background-color: black">
  <p style="color: white;  font-size : 20px;">
    team work with saiyed aadil and ali
  </p>

  <?php
  function myFamily($lastname, ...$firstname)
  {
    $txt = "";
    $len = count($firstname);
    for ($i = 0; $i < $len; $i++) {
      $txt = $txt . "Hi, $firstname[$i] $lastname.<br>";
    }
    return $txt;
  }

  $a = myFamily("Doe", "Mahin", "Aadil", "Ali");
  echo $a;
  
  # Output -1
  # Hi, Mahin Doe.
  # Hi, Aadil Doe.
  # Hi, Ali Doe. 
  ?>
</body>
</html>