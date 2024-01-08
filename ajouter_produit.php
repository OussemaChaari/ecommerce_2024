<!doctype html>
<html lang="en">
<head>
    <?php include 'include/head.php' ?>
    <title>Ajouter produit</title>
</head>
<body>
<?php
require_once 'include/database.php';
include 'include/nav.php' ?>
<div class="container py-2">
    <h4>Ajouter produit</h4>
    <?php
    if (isset($_POST['ajouter'])) {
        $libelle = $_POST['nom'];
        $prix = $_POST['prix'];
        $discount = $_POST['discount'];
        $categorie = $_POST['categorie'];
        $description = $_POST['description'];
        $quantite = $_POST['quantite'];
        $date = date('Y-m-d');

        $filename = 'produit.png';
        if (!empty($_FILES['image']['name'])) {
            $image = $_FILES['image']['name'];
            $filename = uniqid() . $image;
            move_uploaded_file($_FILES['image']['tmp_name'], 'upload/produit/' . $filename);
        }

        if (!empty($libelle) && !empty($prix) && !empty($categorie)) {
            $sqlState = $pdo->prepare('INSERT INTO produits VALUES (null,?,?,?,?,?,?,?,?)');
            $inserted = $sqlState->execute([$libelle,$description, $prix, $discount, $categorie, $date,$quantite,$filename]);
            if ($inserted) {
                header('location: produits.php');
                ?>
                <div class="alert alert-success" role="alert">
                    produit <?= $libelle; ?> est bien ajouter!
                </div>
                <?php
            } else {

                ?>
                <div class="alert alert-danger" role="alert">
                    Database error (40023).
                </div>
                <?php
            }
        } else {
            ?>
            <div class="alert alert-danger" role="alert">
                Libelle , prix , catégorie sont obligatoires.
            </div>
            <?php
        }

    }
    ?>
    <form method="post" enctype="multipart/form-data">
        <label class="form-label">Nom de Produit</label>
        <input type="text" class="form-control" name="nom">
        <label class="form-label">Prix</label>
        <input type="number" class="form-control" step="0.1" name="prix" min="0">
        <label class="form-label">Discount</label>
        <input type="range" value="0" class="form-control" name="discount" min="0" max="90">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="description"></textarea>
        <label class="form-label">Quantité</label>
        <input type="number" class="form-control" name="quantite" id="quantite" max="100" min="0">
        <label class="form-label">Image</label>
        <input type="file" class="form-control" name="image">
        <?php
        $categories = $pdo->query('SELECT * FROM categories')->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <label class="form-label">Catégorie</label>
        <select name="categorie" class="form-control">
            <option value="">Choisissez une catégorie</option>
            <?php
            foreach ($categories as $categorie) {
                echo "<option value='" . $categorie['id'] . "'>" . $categorie['titre'] . "</option>";
            }
            ?>
        </select>


        <input type="submit" value="Ajouter produit" class="btn btn-primary my-2" name="ajouter">
    </form>
</div>

</body>
</html>