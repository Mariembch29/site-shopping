function ajouterAuPanier(nom, marque, prix, image) {
  let panier = JSON.parse(localStorage.getItem("panier")) || [];
  const index = panier.findIndex((p) => p.nom === nom);
  if (index >= 0) {
    panier[index].quantite++;
  } else {
    panier.push({ nom, marque, prix, image, quantite: 1 });
  }
  localStorage.setItem("panier", JSON.stringify(panier));
  updateCartCount();
  alert(nom + " ajouté au panier !");
}

function updateCartCount() {
  const panier = JSON.parse(localStorage.getItem("panier")) || [];
  const total = panier.reduce((sum, item) => sum + item.quantite, 0);
  const el = document.getElementById("cartCount");
  if (el) el.textContent = total;
}

document.addEventListener("DOMContentLoaded", updateCartCount);
