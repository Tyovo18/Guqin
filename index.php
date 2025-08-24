<?php

require 'vendor/autoload.php';

// Définition des namespaces
\EasyRdf\RdfNamespace::set('dbpedia', 'http://dbpedia.org/resource/');
\EasyRdf\RdfNamespace::set('dbo', 'http://dbpedia.org/ontology/');
\EasyRdf\RdfNamespace::set('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');

// Connexion au SPARQL endpoint
$sparql = new \EasyRdf\Sparql\Client('https://dbpedia.org/sparql');

// Définition de la langue par défaut
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'zh']) ? $_GET['lang'] : 'en';

// Requête SPARQL pour récupérer les données en fonction de la langue
$query = "
    SELECT ?label ?abstract ?image WHERE {
        dbr:Guqin rdfs:label ?label ;
                  dbo:abstract ?abstract ;
                  dbo:thumbnail ?image .
        FILTER (lang(?label) = \"$lang\" && lang(?abstract) = \"$lang\")
    }
    LIMIT 1
";

try {
    $result = $sparql->query($query);

    if (count($result) > 0) {
        $data = $result[0];
        $name = (string) $data->label;
        $description = (string) $data->abstract;
        $image = (string) $data->image;
    } else {
        $name = "Données non trouvées";
        $description = "";
        $image = "";
    }
} catch (Exception $e) {
    $name = "Erreur SPARQL";
    $description = $e->getMessage();
    $image = "";
}

?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guqin - Web Sémantique</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }
        .lang-switch {
            margin-top: 20px;
        }
        .lang-switch a {
            text-decoration: none;
            padding: 10px;
            background: #007BFF;
            color: white;
            border-radius: 5px;
            margin: 5px;
            display: inline-block;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Guqin - Web Sémantique</h1>

        <!-- Boutons de sélection de langue -->
        <div class="lang-switch">
            <a href="?lang=en">🇬🇧 English</a>
            <a href="?lang=zh">🇨🇳 中文</a>
        </div>

        <!-- Contenu sémantique annoté avec RDFa -->
        <div vocab="http://schema.org/" typeof="Thing">
            <h2 property="name"><?php echo htmlspecialchars($name); ?></h2>
            <?php if ($image): ?>
                <img property="image" src="<?php echo htmlspecialchars($image); ?>" alt="Image de Guqin">
            <?php endif; ?>
            <p property="description"><?php echo nl2br(htmlspecialchars($description)); ?></p>
        </div>
    </div>

</body>
</html>

