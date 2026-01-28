<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dating App Profile Image Upload Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ffd1dc;
            color: darkslategrey;
            text-align: center;
            padding-top: 50px;
        }
        .result-container {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            background-color: #DE5D83;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            color: darkslategrey;
        }
        .result-container h2 {
            color: darkslategrey;
        }
        .result-container p {
            color: darkslategrey;
        }
        .result-container h3 {
            color: white;
            font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
            font-size: xx-large;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 15px;
        }
        .btn:hover {
            background-color: #0056b3;
        }

        .img-style {
            width: 350px;
            height: 350px;
            border: 2px solid #000;
            border-radius: 10px;
            margin: 20px auto;
            display: block;
        }

    </style>
</head>
<body>
    <div class="result-container">



        <?php
        
        $imagePath = $_FILES["image"]["tmp_name"];
        $pythonScript = 'analyze.py';
        

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileType = $_FILES["image"]["type"];


                
                
                if (in_array($fileType, $allowedTypes)) {
                    $imagePath = $_FILES["image"]["tmp_name"];
                    $imageName = $_FILES["image"]["name"];

                    // Read the file
                    $imageData = file_get_contents($imagePath);
                    // Encode the image in base64
                    $base64Image = base64_encode($imageData);
                    // Prepare the src attribute for the img tag
                    $imageSrc = 'data:' . $fileType . ';base64,' . $base64Image;
                    
                   
                    $command = escapeshellcmd("python3 analyze.py " . escapeshellarg($imagePath));
                    $output = shell_exec($command);

                    
                    

                
                    // Continue with your image grading logic
                    $imageSize = getimagesize($imagePath);
                    $imageResource = imagecreatefromstring(file_get_contents($imagePath));
                    
                    $imageSize = getimagesize($imagePath);
                    $imageResource = imagecreatefromstring(file_get_contents($imagePath));

                    if ($imageSize !== false && $imageResource !== false) {
                        $gradeResolution = gradeResolution($imageSize[0], $imageSize[1]);
                        $gradeBrightness = gradeBrightness($imageResource);

                        echo "<p>Resolution Grade: " . $gradeResolution . "</p>";
                        echo "<p>Brightness Grade: " . $gradeBrightness . "</p>";

                    } else {
                        echo "<p>Invalid image file.</p>";
                    }
                } else {
                    echo "<p>Only JPEG, PNG, and GIF files are allowed.</p>";
                }

            } else {
                echo "<p>Error in file upload.</p>";
            }
        }

        function gradeResolution($width, $height) {
            $pixels = $width * $height;

            if ($pixels >= 8000000) { // 8MP or higher
                return 'A';
            } elseif ($pixels >= 2000000) { // 2MP - 8MP
                return 'B';
            } elseif ($pixels >= 500000) { // 0.5MP - 2MP
                return 'C';
            } else {
                return 'D';
            }
        }

        function gradeBrightness($imageResource) {
            $width = imagesx($imageResource);
            $height = imagesy($imageResource);
            $totalBrightness = 0;

            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $rgb = imagecolorat($imageResource, $x, $y);
                    $colors = imagecolorsforindex($imageResource, $rgb);
                    $brightness = ($colors['red'] + $colors['green'] + $colors['blue']) / 3;
                    $totalBrightness += $brightness;
                }
            }

            $averageBrightness = $totalBrightness / ($width * $height);

            if ($averageBrightness >= 200) { // Bright image
                return 'A';
            } elseif ($averageBrightness >= 100) { // Moderately bright
                return 'B';
            } elseif ($averageBrightness >= 50) { // Low brightness
                return 'C';
            } else {
                return 'D'; // Very dark image
            }
            
        }

        function getAverageColorAndGrade($imagePath) {
            $image = imagecreatefromstring(file_get_contents($imagePath));

            if (!$image) {
                return false;
            }
        
            $width = imagesx($image);
            $height = imagesy($image);
        
            $totalRed = $totalGreen = $totalBlue = 0;
            $totalPixels = $width * $height;
        
            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $rgb = imagecolorat($image, $x, $y);
                    $totalRed += ($rgb >> 16) & 0xFF;
                    $totalGreen += ($rgb >> 8) & 0xFF;
                    $totalBlue += $rgb & 0xFF;
                }
            }
        
            $avgRed = round($totalRed / $totalPixels);
            $avgGreen = round($totalGreen / $totalPixels);
            $avgBlue = round($totalBlue / $totalPixels);
        
            // Calculate luminance
            $luminance = 0.299 * $avgRed + 0.587 * $avgGreen + 0.114 * $avgBlue;
        
            // Determine grade based on luminance
            $grade = $luminance > 180 ? 'A' :
                     ($luminance > 130 ? 'B' :
                     ($luminance > 80 ? 'C' : 'D'));
        
            imagedestroy($image);
        
            return [
                'red' => $avgRed,
                'green' => $avgGreen,
                'blue' => $avgBlue,
                'grade' => $grade
            ];
        }
        
        function gradeToNumeric($grade) {
            $map = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1];
            return $map[$grade] ?? 0; // Default to 0 if grade not found
        }
        
        function calculateAverageGrade($grades) {
            $total = 0;
            foreach ($grades as $grade) {
                $total += gradeToNumeric($grade);
            }
            return $total / count($grades); // Calculate average
        }
        
        function numericToLetterGrade($averageNumeric) {
            if ($averageNumeric >= 3.5) {
                return 'A';
            } elseif ($averageNumeric >= 2.5) {
                return 'B';
            } elseif ($averageNumeric >= 1.5) {
                return 'C';
            } elseif ($averageNumeric >= 0.5) {
                return 'D';
            } else {
                return 'F'; // Assuming F is a possible grade
            }
        }
        
       
        




        // Collect grades
        $grades = [];
        $imageSize = getimagesize($imagePath);
        $imageResource = imagecreatefromstring(file_get_contents($imagePath));
        
        if ($imageSize !== false && $imageResource !== false) {
            $grades[] = gradeResolution($imageSize[0], $imageSize[1]);
            $grades[] = gradeBrightness($imageResource);
            // Add more grades if needed
            $averageNumericGrade = calculateAverageGrade($grades);
        } else {
            echo "<p>Invalid image file.</p>";
        }
        
       
        // Adjusted example usage
        $imagePath = $_FILES["image"]["tmp_name"]; // Use the uploaded image
        $result = getAverageColorAndGrade($imagePath);
        if ($result) {
            echo "Coloring Grade: ", $result['grade'];
        } else {
            echo "Unable to calculate average color or grade.";
        }
    
        $letterGrade = numericToLetterGrade($averageNumericGrade);
        echo "<hr>";
        echo "<h3>Overall Image Grade: " . $letterGrade . "</h3>";


        $imagePath = $_FILES["image"]["tmp_name"]; // Use the uploaded image
        echo "<img src='" . $imageSrc . "' alt='Uploaded dating Image' class='img-style'/>";



        ?>
    </div>

    

</body>
</html>
