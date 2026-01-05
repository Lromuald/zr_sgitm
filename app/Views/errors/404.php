<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Non Trouvée | ZRSGIMT</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #240046 0%, #ff5400 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        .error-container {
            background: white;
            padding: 60px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 600px;
        }
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #240046;
            margin-bottom: 20px;
            text-shadow: 3px 3px 0 #ff5400;
        }
        .error-message {
            font-size: 24px;
            color: #555;
            margin-bottom: 15px;
        }
        .error-description {
            font-size: 16px;
            color: #777;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            background: #240046;
            color: white;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: #ff5400;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 84, 0, 0.4);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <div class="error-message">Page Non Trouvée</div>
        <div class="error-description">
            Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
            Veuillez vérifier l'URL ou retourner à l'accueil.
        </div>
        <a href="<?php echo BASE_URL; ?>/" class="btn">Retour à l'accueil</a>
    </div>
</body>
</html>
