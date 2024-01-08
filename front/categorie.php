<?php 
require_once '../include/database.php';
$id = $_GET['id'];
$sqlState = $pdo->prepare("SELECT * FROM categories WHERE id=?");
$sqlState->execute([$id]);
$categorie = $sqlState->fetch(PDO::FETCH_ASSOC); 

$sqlState = $pdo->prepare("SELECT * FROM produits WHERE id_categorie=?");
$sqlState->execute([$id]);
$produits = $sqlState->fetchAll(PDO::FETCH_OBJ);

?>
<!doctype html>
<html lang="en">
<head>
    <?php include '../include/head_front.php' ?>
    <title>categorie | <?= $categorie['titre']; ?></title>

</head>
<body>
<?php include '../include/nav_front.php' ?>
<div class="container py-2">

<h4><?php echo $categorie['titre'] ?> <span class="fa <?php echo $categorie['icone'] ?>"></span></h4>
<div class="row col-md-12">

<?php
foreach ($produits as $produit) {
    $idProduit = $produit->id;
    ?>
    <div class="col-md-6 mb-4">
        <div class="card h-100">

            <?php if (!empty($produit->discount)): ?>
                <span class="badge rounded-pill text-bg-warning w-25 position-absolute m-2" style="right:0"> - <?= $produit->discount ?> <i
                            class="fa fa-percent"></i></span>
            <?php endif; ?>

            <img class="card-img-top w-100" height="300" src="../upload/produit/<?= $produit->image ?>"
                 alt="Card image cap">
            <div class="card-body">
                <a href="produit.php?id=<?php echo $idProduit ?>" class="btn stretched-link">Afficher details</a>
                <h5 class="card-title"><?= $produit->nom ?></h5>
                <p class="card-text"><?= $produit->description ?></p>
                <p class="card-text"><small class="text-muted">Ajouté le
                        : <?= date_format(date_create($produit->date_creation), 'Y/m/d') ?></small></p>
            </div>
            <div class="card-footer bg-white" style="z-index: 10">
                <?php include '../include/front/counter.php'; ?>
                <?php if (!empty($produit->discount)): ?>
                    <div class="h5"><span
                                class="badge rounded-pill text-bg-danger"><strike> <?= $produit->prix ?></strike> <i
                                    class="fa fa-solid fa-dollar"></i></span></div>
                    <div class="h5"><span
                                class="badge rounded-pill text-bg-success">Solde : <i
                                    class="fa fa-solid fa-dollar"></i></span></div>
                <?php else: ?>
                    <div class="h5"><span class="badge rounded-pill text-bg-success"><?= $produit->prix ?> <i
                                    class="fa fa-solid fa-dollar"></i></span></div>

                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}
if (empty($produits)) {
    ?>
    <div class="alert alert-info" role="alert">
        Pas de produits pour l'instant
    </div>

    <?php
}
?>
</div>
</div>

</body>
</html>