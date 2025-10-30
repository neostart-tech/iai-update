# 📋 GUIDE D'UTILISATION - FRAIS DIFFÉRENCIÉS PAR GENRE

## 🎯 FONCTIONNALITÉ IMPLÉMENTÉE

✅ **Paramétrage DAF des frais différenciés pour hommes et femmes**

Le système SEMOA permet maintenant au **Directeur des Affaires Financières (DAF)** de configurer des frais de scolarité différents selon le genre des étudiants.

---

## 🚀 ACCÈS À LA FONCTIONNALITÉ

### 1️⃣ **Connexion DAF**
- Se connecter avec un compte ayant le rôle `Directeur des Affaires Financières`
- Accéder au menu DAF dans la sidebar

### 2️⃣ **Interface de Configuration**
- **URL :** `/daf/frais-genre`
- **Menu :** DAF > Frais par Genre > Configuration

---

## 🛠️ UTILISATION DE L'INTERFACE

### **Tableau de Bord Statistiques**
- 👨 **Frais Hommes :** Nombre de configurations pour les hommes
- 👩 **Frais Femmes :** Nombre de configurations pour les femmes  
- 👥 **Frais Mixtes :** Configurations pour tous les étudiants
- 📊 **Total :** Nombre total de configurations

### **Formulaire de Configuration**
1. **Sélectionner le niveau** (L1, L2, M1, etc.)
2. **Définir les montants :**
   - 💰 **Montant Hommes :** Frais spécifiques aux étudiants
   - 💰 **Montant Femmes :** Frais spécifiques aux étudiantes
   - 💰 **Montant Mixte :** Frais pour tous (optionnel)
3. **Ajouter une description** explicative
4. **Cliquer sur "Configurer les Frais"**

### **Liste des Configurations**
- Visualisation de toutes les configurations actuelles
- Filtrage par année, niveau et genre
- Actions d'édition et suppression

---

## 📊 RAPPORT ET ANALYSE

### **Accès au Rapport**
- **URL :** `/daf/frais-genre/rapport`
- **Menu :** DAF > Frais par Genre > Rapport & Analyse

### **Contenu du Rapport**
- **Tableau comparatif** par niveau
- **Différence H-F** calculée automatiquement
- **Statut de configuration** (Complet/Mixte/Manquant)
- **Graphiques d'analyse :**
  - Répartition par type (camembert)
  - Comparaison des montants (barres)

---

## ⚙️ LOGIQUE TECHNIQUE

### **Calcul des Frais selon le Genre**
```php
// Le système sélectionne automatiquement les frais appropriés
$frais = FraisScolarite::getFraisForEtudiant($niveauId, $genre, $anneeScolaireId);

// Priorité : Genre spécifique > Frais mixtes ("Tous")
```

### **Modèle de Données Étendu**
```sql
ALTER TABLE frais_scolarites ADD COLUMN genre ENUM('Masculin','Féminin','Tous') DEFAULT 'Tous';
ALTER TABLE frais_scolarites ADD COLUMN description TEXT NULL;
```

---

## 🔒 SÉCURITÉ ET PERMISSIONS

### **Middleware de Protection**
- Accès limité aux utilisateurs avec rôle DAF
- Vérification automatique des permissions
- Redirection sécurisée si accès non autorisé

### **Validation des Données**
- Contrôle d'unicité (niveau + genre + année)
- Validation des montants (nombres positifs)
- Échappement des descriptions

---

## 💡 EXEMPLES D'UTILISATION

### **Cas 1 : Frais Différenciés**
- **L1 Hommes :** 150,000 F
- **L1 Femmes :** 120,000 F (réduction de 30,000 F)
- **Description :** "Politique de soutien aux étudiantes"

### **Cas 2 : Frais Mixtes**
- **M1 Tous :** 180,000 F
- **Description :** "Tarif unique pour le Master 1"

### **Cas 3 : Configuration Partielle**
- **L3 Femmes :** 100,000 F (promotion spéciale)
- **L3 Hommes :** Non configuré (utilise les frais par défaut)

---

## 🎉 BÉNÉFICES DE LA FONCTIONNALITÉ

1. **✨ Flexibilité** : Configuration par niveau et genre
2. **📈 Suivi** : Rapports et statistiques détaillés
3. **🔐 Sécurité** : Accès contrôlé par rôle DAF
4. **🎯 Précision** : Calcul automatique selon le profil étudiant
5. **📊 Analyse** : Visualisation graphique des différences
6. **⚡ Simplicité** : Interface intuitive et conviviale

---

## 🚨 NOTES IMPORTANTES

- ⚠️ **Suppression** : Supprimer une configuration supprime automatiquement les anciens frais
- 🔄 **Mise à jour** : Les nouveaux frais s'appliquent immédiatement
- 👥 **Priorité** : Les frais spécifiques au genre prennent la priorité sur les frais mixtes
- 📅 **Année scolaire** : Les configurations sont liées à l'année scolaire active

---

## ✅ VALIDATION RÉALISÉE

| **Exigence** | **Statut** | **Détail** |
|--------------|------------|-------------|
| Paramétrage DAF | ✅ **IMPLÉMENTÉ** | Interface complète avec middleware de sécurité |
| Frais différenciés H/F | ✅ **IMPLÉMENTÉ** | Configuration par niveau avec calcul automatique |
| Rapports et analyses | ✅ **IMPLÉMENTÉ** | Tableaux comparatifs et graphiques interactifs |
| Intégration système | ✅ **IMPLÉMENTÉ** | Mise à jour des contrôleurs de paiement |

**🎊 FONCTIONNALITÉ 100% OPÉRATIONNELLE**