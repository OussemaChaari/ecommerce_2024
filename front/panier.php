<?php
session_start();
require_once '../include/database.php';
?>
<!doctype html>
<html lang="en">

<head>
    <?php include '../include/head_front.php' ?>
    <title>Panier</title>
</head>

<body>
    <?php include '../include/nav_front.php' ?>
    <div class="container py-2">
        <?php
        if (isset($_POST['vider'])) {
            $_SESSION['panier'][$idUtilisateur] = [];
            header('location: panier.php');
        }
        $idUtilisateur = $_SESSION['utilisateur']['id'] ?? 0;
        $panier = $_SESSION['panier'][$idUtilisateur] ?? [];
        if (!empty($panier)) {
            $idProduits = array_keys($panier);
            $idProduits = implode(',', $idProduits);
            $produits = $pdo->query("SELECT * FROM produits WHERE id IN ($idProduits)")->fetchAll(PDO::FETCH_ASSOC);
        }
        if (isset($_POST['valider'])) {
            $total = 0;
            $prixProduits = [];
            // Calculate total and prepare data for insertion
            foreach ($produits as $produit) {
                $idProduit = $produit['id'];
                $qty = $panier[$idProduit];
                if ($produit['quantité'] <= 0 || $produit['quantité'] < $qty) {
                    ?>
                    <div class="alert alert-warning" role="alert">
                        La quantité pour le produit <?= $produit['nom'] ?> est insuffisante.
                    </div>
                    <?php
                    exit; // Stop processing the order if any product has a quantity of 0 or less in the original produits table
                }
                $discount = $produit['discount'];
                $prix = calculerRemise($produit['prix'], $discount);
                $total += $qty * $prix;
                $prixProduits[] = [
                    'id' => $idProduit,
                    'prix' => $prix,
                    'total' => $qty * $prix,
                    'qty' => $qty
                ];
            }
            // Insert into commande table
            $sqlCommande = 'INSERT INTO commande (id_client, total) VALUES (?, ?)';
            $stmtCommande = $pdo->prepare($sqlCommande);
            $stmtCommande->execute([$idUtilisateur, $total]);
            $idCommande = $pdo->lastInsertId();        
            // Insert into ligne_commande table
            $sqlLigneCommande = 'INSERT INTO ligne_commande (id_produit, id_commande, prix, quantité, total) VALUES (?, ?, ?, ?, ?)';
            $stmtLigneCommande = $pdo->prepare($sqlLigneCommande);
            // Execute the insert for each produit
            foreach ($prixProduits as $produit) {
                $stmtLigneCommande->execute([$produit['id'], $idCommande, $produit['prix'], $produit['qty'], $produit['total']]);
                $updateProduitQty = 'UPDATE produits SET quantité = quantité - ? WHERE id = ?';
                $stmtUpdateProduitQty = $pdo->prepare($updateProduitQty);
                $stmtUpdateProduitQty->execute([$produit['qty'], $produit['id']]);
            }
            // Check if the inserts were successful
            if ($stmtLigneCommande) {
                $_SESSION['panier'][$idUtilisateur] = [];
                header('location: panier.php?success=true&total=' . $total);
            } else {
                ?>
                <div class="alert alert-error" role="alert">
                    Erreur (contactez l'administrateur).
                </div>
                <?php
            }
        }
        if (isset($_GET['success'])) {
            ?>
            <h1>Merci ! </h1>
            <div class="alert alert-success" role="alert">
                Votre commande avec le montant <strong>(
                    <?php echo $_GET['total'] ?? 0 ?>)
                </strong> <i class="fa fa-solid fa-dollar"></i> est bien ajoutée.
            </div>
            <hr>
            <?php
        }
        if (!isset($_GET['success'])) {

            ?>
            <h4>Panier (
                <?php echo $productCount; ?>)
            </h4>
            <?php
        }
        ?>
        <div class="container">
            <div class="row">
                <?php
                if (empty($panier)) {
                    if (!isset($_GET['success'])) {
                        ?>
                        <div class="alert alert-warning" role="alert">
                            Votre panier est vide !
                            Commençez vos achats <a class="btn btn-success btn-sm" href="./index.php">Acheter des
                                produits</a>
                        </div>
                        <?php
                    }
                } else {

                    ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Image</th>
                                <th scope="col">Libelle</th>
                                <th scope="col">Quantité</th>
                                <th scope="col">Prix</th>
                                <th scope="col">Remise</th>
                                <th scope="col"><i class="fa fa-percent"></i> prix remise</th>
                                <th scope="col">Total</th>
                            </tr>
                        </thead>
                        <?php
                        $total = 0;
                        foreach ($produits as $produit) {
                            $idProduit = $produit['id'];
                            $totalProduit = calculerRemise($produit['prix'], $produit['discount']) * $panier[$idProduit];
                            $total += $totalProduit;
                            ?>
                            <tr>
                                <td>
                                    <?php echo $produit['id'] ?>
                                </td>
                                <td><img width="80px" src="../upload/produit/<?php echo $produit['image'] ?>" alt=""></td>
                                <td>
                                    <?php echo $produit['nom'] ?>
                                </td>
                                <td>
                                    <?php include '../include/front/counter.php' ?>
                                </td>
                                <td><strike>
                                        <?php echo $produit['prix'] ?> <i class="fa fa-solid fa-dollar"></i>
                                    </strike></td>
                                <td> -
                                    <?= $produit['discount'] ?> %
                                </td>
                                <td>
                                    <?php echo calculerRemise($produit['prix'], $produit['discount']) ?> <i
                                        class="fa fa-solid fa-dollar"></i>
                                </td>
                                <td>
                                    <?php echo $totalProduit ?> <i class="fa fa-solid fa-dollar"></i>
                                </td>
                            </tr>

                            <?php
                        }
                        ?>
                        <tfoot>
                            <tr>
                                <td colspan="7" align="right"><strong>Total</strong></td>
                                <td>
                                    <?php echo $total ?> <i class="fa fa-solid fa-dollar"></i>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8" align="right">
                                    <form method="post">
                                        <input type="submit" class="btn btn-success" name="valider"
                                            value="Valider la commande">
                                        <input onclick="return confirm('Voulez vous vraiment vider le panier ?')"
                                            type="submit" class="btn btn-danger" name="vider" value="Vider le panier">
                                    </form>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</body>