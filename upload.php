<?php
  session_start();
  if (!isset($_SESSION['username'])) {
    header("Location: session.php");
    exit;
  }

  // Connect to the SQLite database
  $db = new SQLite3('database.sqlite');

  // Check if an image was uploaded
  if (isset($_FILES['image'])) {
    $image = $_FILES['image'];

    // Check if the image is valid
    if ($image['error'] == 0) {
      // Generate a unique file name
      $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
      $filename = uniqid() . '.' . $ext;

      // Save the original image
      move_uploaded_file($image['tmp_name'], 'images/' . $filename);

      // Determine the image type and generate the thumbnail
      switch ($ext) {
        case 'jpg':
        case 'jpeg':
          $source = imagecreatefromjpeg('images/' . $filename);
          break;
        case 'png':
          $source = imagecreatefrompng('images/' . $filename);
          break;
        case 'gif':
          $source = imagecreatefromgif('images/' . $filename);
          break;
        default:
          echo "Error: Unsupported image format.";
          exit;
      }

      $thumbnail = imagecreatetruecolor(100, 100);
      imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, 100, 100, imagesx($source), imagesy($source));

      switch ($ext) {
        case 'jpg':
        case 'jpeg':
          imagejpeg($thumbnail, 'thumbnails/' . $filename);
          break;
        case 'png':
          imagepng($thumbnail, 'thumbnails/' . $filename);
          break;
        case 'gif':
          imagegif($thumbnail, 'thumbnails/' . $filename);
          break;
        default:
          echo "Error: Unsupported image format.";
          exit;
      }

      // Add the image to the database
      $username = $_SESSION['username'];
      $stmt = $db->prepare("INSERT INTO images (username, filename) VALUES (:username, :filename)");
      $stmt->bindValue(':username', $username);
      $stmt->bindValue(':filename', $filename);
      $stmt->execute();

      header("Location: index.php");
      exit;
    } else {
      echo "Error uploading image.";
    }
  }
?>
