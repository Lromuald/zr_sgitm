<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Refusé | ZRSGIMT</title>
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
        .error-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
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
        <div class="error-icon">🚫</div>
        <div class="error-message">Accès Refusé</div>
        <div class="error-description">
            Vous n'avez pas les permissions nécessaires pour accéder à cette page.
            Veuillez contacter l'administrateur si vous pensez qu'il s'agit d'une erreur.
        </div>
        <a href="<?php echo BASE_URL; ?>/dashboard" class="btn">Retour au tableau de bord</a>
    </div>
</body>
</html>
