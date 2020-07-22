<?php
ob_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hawthorn Foundation Role Change</title>
    <style>
        * {
            font-family: 'Open Sans', Arial, sans-serif;
        }

        body {
            background-color: #3E7127;
        }
        table {
            margin: 0 auto;
            background-color: white;
        }

        a {
            color: #3E7127;
        }
    </style>
</head>
<body>
    <table align="center" width="700" style="width: 700px;">
        <tr>
            <h1>The Hawthorn Foundation</h1>
        </tr>
        <tr>
            <td>Your user role has been changed!</td>
        </tr>
        <tr>
            <td>To fill out a Supplemental Application please visit | </td>
            <td><a href="https://thehawthornfoundation.org/supplemental-scholarship-application/">The Supplemental Application Page</a></td>
        </tr>
    </table>
</body>
</html>
<?php
return ob_get_clean();