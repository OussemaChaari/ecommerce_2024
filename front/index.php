<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
    <?php include '../include/head_front.php' ?>
    <title>Accueil</title>
</head>
<body>
<?php include '../include/nav_front.php' ?>
<div class="container py-2">
<?php
    require_once '../include/database.php';
    $categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_OBJ); 
    ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <ul class="list-group list-group-flush position-sticky sticky-top">
                    <h4 class=" mt-4"><i class="fa fa-light fa-list"></i> Liste des catégories</h4>
                    <?php
                    foreach ($categories as $categorie) {
                        ?>
                        <li class="list-group-item">
                            <a class="btn btn-default w-100" style="text-align:left;"
                               href="categorie.php?id=<?php echo $categorie->id ?>"> <i class="fa <?php echo $categorie->icone ?>"></i>
                                <?php echo $categorie->titre ?>
                            </a>
                        </li>
                        <?php
                    }
                    ?>          
                </ul>
            </div>
            <div class="col mt-4">
                <div class="row">
                </div>
            </div>
        </div>
    </div>

</div>

</body>
</html>