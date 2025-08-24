# Guqin RDF Explorer  

Projet en PHP utilisant [EasyRDF](https://www.easyrdf.org/) et [DBpedia](https://dbpedia.org/) pour récupérer et afficher des données RDF sur le **Guqin** (instrument de musique traditionnel chinois).  

---

## Étape 1 : Préparation de l’environnement  

### Installation des outils  
[Guide complet sur Composer](https://blog.crea-troyes.fr/1724/composer-le-tutoriel-complet-installation-et-utilisation/)  

1. Vérifier la version de PHP (≥ 7) :  
   ```bash
   php -v
   ```

2. Installer Composer (gestionnaire de dépendances PHP) :  
   ```bash
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   ```

3. Créer le projet et installer **EasyRDF** :  
   ```bash
   mkdir Guqin && cd Guqin
   composer require easyrdf/easyrdf
   ```

---

## Étape 2 : Récupérer les données RDF avec SPARQL  

1. Ouvrir [DBpedia SPARQL Explorer](https://dbpedia.org/sparql)  
2. Page de référence Guqin : [DBpedia/Guqin](https://dbpedia.org/page/Guqin)  
3. Filtrer par langue : [Exemple filtre](https://www.bobdc.com/blog/filterforeignliterals/)  

### Exemple de requête SPARQL
```sparql
SELECT ?label ?abstract ?image WHERE {
  dbr:Guqin rdfs:label ?label ;
            dbo:abstract ?abstract ;
            dbo:thumbnail ?image .
  FILTER (lang(?label) = "en" && lang(?abstract) = "en")
}
```

### Exemple en PHP (`test.php`)
Inspiré de : [basic_sparql.php](https://github.com/easyrdf/easyrdf/blob/main/examples/basic_sparql.php)  

```php
<?php
require 'vendor/autoload.php';
\EasyRdf\RdfNamespace::set('dbpedia', 'http://dbpedia.org/resource/');
\EasyRdf\RdfNamespace::set('dbo', 'http://dbpedia.org/ontology/');
$sparql = new \EasyRdf\Sparql\Client('https://dbpedia.org/sparql');

$query = '
    SELECT ?label ?abstract ?image WHERE {
      dbr:Guqin rdfs:label ?label ;
                dbo:abstract ?abstract ;
                dbo:thumbnail ?image .
      FILTER (lang(?label) = "en" && lang(?abstract) = "en")
    }
';

$result = $sparql->query($query);
$data = $result[0];

echo json_encode([
    'name' => $data->label,
    'description' => $data->abstract,
    'image' => $data->image
], JSON_PRETTY_PRINT);
```

Test du script :  
```bash
php test.php
```

---

## Étape 3 : Génération de la page web  

### Exemple `index.php`
```php
<?php
require 'vendor/autoload.php';

\EasyRdf\RdfNamespace::set('dbpedia', 'http://dbpedia.org/resource/');
\EasyRdf\RdfNamespace::set('dbo', 'http://dbpedia.org/ontology/');
$sparql = new \EasyRdf\Sparql\Client('https://dbpedia.org/sparql');

$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'zh']) ? $_GET['lang'] : 'en';

$query = "
    SELECT ?label ?abstract ?image WHERE {
        dbr:Guqin rdfs:label ?label ;
                  dbo:abstract ?abstract ;
                  dbo:thumbnail ?image .
        FILTER (lang(?label) = \"$lang\" && lang(?abstract) = \"$lang\")
    }
    LIMIT 1
";

$result = $sparql->query($query);
$data = $result[0];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $data->label ?></title>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "MusicalInstrument",
        "name": "<?= $data->label ?>",
        "description": "<?= $data->abstract ?>",
        "image": "<?= $data->image ?>"
    }
    </script>
    <style>
      body { font-family: sans-serif; max-width: 700px; margin: auto; }
      h1 { color: #444; }
      img { max-width: 100%; border-radius: 10px; }
    </style>
</head>
<body>
    <h1><?= $data->label ?></h1>
    <p><?= $data->abstract ?></p>
    <img src="<?= $data->image ?>" alt="Guqin">

    <div>
        <a href="?lang=en">🇬🇧 English</a> | 
        <a href="?lang=zh">🇨🇳 中文</a>
    </div>
</body>
</html>
```

Lancer un serveur local :  
```bash
php -S localhost:8000
```

---

## ⚠️ Problèmes rencontrés  

- Erreurs liées aux extensions manquantes (`dom`, `mbstring`, `xmlreader`).  

👉 Solution : installer les modules PHP manquants :  
```bash
sudo apt update
sudo apt install php-xml php-mbstring
```

Vérifier qu’ils sont bien activés :  
```bash
php -m | grep -E 'dom|mbstring|xmlreader'
```

Puis relancer l’installation de EasyRDF :  
```bash
composer require easyrdf/easyrdf
```

---

## ✅ Résultat attendu  

- Page web affichant **titre, description et image du Guqin** depuis DBpedia.  
- Boutons pour basculer entre **anglais** et **chinois**.  
- Données RDF intégrées au format **JSON-LD** pour compatibilité avec [Schema.org](https://schema.org/).  
