<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Translator</title>
    <link rel="icon" href="Screenshot_2025-05-01_at_11.42.04_PM-removebg.png" type="image/x-icon">
</head>

<style>

    body {
        background-color: #552a6c;
        font-family: Arial, sans-serif;
        color: #44226d;
        text-align: center;
        padding: 50px;
        overflow: hidden;
        
    }

    h1 {
        color: #2c3e50;
    }
    .container {
        max-width: 600px;
        height: 400px;
        margin: auto;
        margin-top: 0%;
        padding: 20px;
        background-color: #cfcccc;
        border-radius: 15px;
        box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
    }
    form {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin-top: 20px;
    }
    #text {
        width: 80%;
        height: 110px;
        padding: 10px;
        border-radius: 8px;
        border: 2px solid #ccc;
        margin-bottom: 0px;
        background-color: rgb(255, 255, 255);
        color: #44226d;
        resize: none;
    }
    label{
        font-size: 18px;
        color: #44226d;
    }
    #language{
        width: 30%;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #663f6d;
        margin-bottom: 20px;
        background-color: rgb(255, 255, 255);
        color: #44226d;
        text-align: center;
    }
    #buttons {
        display: flex;
        justify-content: space-between;
        width: 40%;
        margin-top: -20px;
        margin-bottom: 20px;
    }
    #btn{
        padding: 10px 20px;
        border-radius: 5px;
        border: none;
        background-color: #44226d;
        color: white;
        cursor: pointer;
        font-size: 12px;
    }
    #btn:hover {
        background-color: #663f6d;
    }
    .last {
        max-width: 600px;
        height: 90px;
        margin: auto;
        margin-top: 1%;
        padding: 20px;
        background-color: #cfcccc;
        border-radius: 15px;
        box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
    }
    #show {
        font-size: 18px;
        margin-top: -2%;
        color: #44226d;
        background-color: white;
        border: 1px solid #44226d;  
        height: 114px;
        border-radius: 20px;
        width: 100%;
    }
   


</style>

<body>
    <div class="container">
        <h1>Translator</h1>
        <form action="translate.php" method="post">
            <label for="text">Enter text to translate:</label><br>
            <textarea id="text" name="text" rows="4" cols="50" required></textarea><br><br>
            <select id="language" name="language" required>
                <option value="">--Select Language--</option>
                <option value="en">English</option>
                <option value="es">Spanish</option>
                <option value="fr">French</option>
                <option value="de">German</option>
            </select><br><br>
            <div id="buttons">
                <input id="btn" type="submit" value="Translate">
                <input id="btn" type="reset" value="Clear"> 
            </div>
        </form>
    </div>
    <div class="last">
        <div id="show"></div>
    </div>
   
</body>
</html>
<?php

?>
