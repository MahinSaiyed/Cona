<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>

<body style="background-color: black">
  <p style="color: white;  font-size : 20px;">
    Team Work With Saiyed Aadil And Ali
  </p><hr>

  <?php
  // PHP se time ki jagah ek empty span de do
  echo '<span id="time" style="color: white;"></span>';
?>
<script>
  function updateTime() {
    const now = new Date();
    let hours = now.getHours();
    let minutes = now.getMinutes();
    let seconds = now.getSeconds();
    let ampm = hours >= 12 ? 'pm' : 'am';
    hours = hours % 12;
    hours = hours ? hours : 12; // 0 ko 12 bana do
    minutes = minutes < 10 ? '0'+minutes : minutes;
    seconds = seconds < 10 ? '0'+seconds : seconds;
    const timeStr = hours + ':' + minutes + ':' + seconds + ampm;
    document.getElementById('time').textContent = 'The time is ' + timeStr;
  }
  setInterval(updateTime, 1000);
  updateTime();
</script>

</body>
</html>