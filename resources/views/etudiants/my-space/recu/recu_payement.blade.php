<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de Paiement SEMOA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        @media print {
            body {
                width: 210mm;
                height: 297mm;
                margin: 0;
                padding: 10mm;
                background: white;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg p-8 mb-8">
        <!-- En-tête avec logo et informations -->
        <div class="flex justify-between items-center border-b pb-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">REÇU DE PAIEMENT</h1>
                <p class="text-gray-600">SEMOA - Service des Étudiants et des Mobilités Académiques</p>
            </div>
            <div class="text-right">
                <img src="https://placehold.co/150x60" alt="Logo SEMOA avec écrit 'SEMOA' en bleu foncé et icône de livre ouvert" />
                <p class="text-sm text-gray-500 mt-2">Reçu N°: SEMOA-<span id="receiptNumber">20230815-001</span></p>
                <p class="text-sm text-gray-500">Date: <span id="receiptDate">15/08/2023</span></p>
            </div>
        </div>

        <!-- Informations étudiant -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-3">Informations de l'Étudiant</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600 font-medium">Nom Complet:</p>
                    <p class="text-gray-800 border-b pb-1" id="studentName">Jean Dupont</p>
                </div>
                <div>
                    <p class="text-gray-600 font-medium">Matricule:</p>
                    <p class="text-gray-800 border-b pb-1" id="studentId">ETU123456</p>
                </div>
                <div>
                    <p class="text-gray-600 font-medium">Établissement:</p>
                    <p class="text-gray-800 border-b pb-1" id="school">Université de Yaoundé I</p>
                </div>
                <div>
                    <p class="text-gray-600 font-medium">Filière:</p>
                    <p class="text-gray-800 border-b pb-1" id="program">Informatique</p>
                </div>
            </div>
        </div>

        <!-- Détails du paiement -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-3">Détails du Paiement</h2>
            <div class="border rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800">Frais de scolarité</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 text-right" id="tuitionFee">50 000 FCFA</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800">Frais d'inscription</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 text-right" id="registrationFee">10 000 FCFA</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800">Frais de bibliothèque</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 text-right" id="libraryFee">5 000 FCFA</td>
                        </tr>
                        <tr class="bg-gray-100 font-semibold">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800">Total</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 text-right" id="totalAmount">65 000 FCFA</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Informations de transaction -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-3">Informations de Transaction</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600 font-medium">Référence de paiement:</p>
                    <p class="text-gray-800 border-b pb-1" id="paymentRef">PAY-20230815-7894</p>
                </div>
                <div>
                    <p class="text-gray-600 font-medium">Mode de paiement:</p>
                    <p class="text-gray-800 border-b pb-1" id="paymentMethod">Mobile Money (MTN)</p>
                </div>
                <div>
                    <p class="text-gray-600 font-medium">Numéro de transaction:</p>
                    <p class="text-gray-800 border-b pb-1" id="transactionId">TRX789456123</p>
                </div>
                <div>
                    <p class="text-gray-600 font-medium">Date de transaction:</p>
                    <p class="text-gray-800 border-b pb-1" id="transactionDate">15/08/2023 14:30</p>
                </div>
            </div>
        </div>

        <!-- Mentions importantes -->
        <div class="border-t pt-4 mb-8">
            <h3 class="font-semibold text-gray-700 mb-2">Mentions Importantes</h3>
            <ul class="list-disc pl-5 text-sm text-gray-600 space-y-1">
                <li>Ce reçu doit être présenté à l'établissement pour compléter le processus d'enrégistrement.</li>
                <li>Toute réclamation doit être faite dans les 15 jours suivant la date de paiement.</li>
                <li>Conservez ce document précieusement, il fait foi de paiement.</li>
            </ul>
        </div>

        <!-- Signature et cachet -->
        <div class="flex justify-between pt-12">
            <div class="text-center">
                <div class="h-20 mb-2 relative">
                    <img src="https://placehold.co/150x80" alt="Cachet officiel SEMOA avec inscription 'SEMOA' et logo éducation" class="mx-auto opacity-70" />
                </div>
                <p class="text-sm text-gray-500 border-t-2 border-gray-300 pt-2">Pour le Service Comptable SEMOA</p>
            </div>
            <div class="text-center">
                <div class="h-20 mb-2 relative">
                    <p class="text-xs text-gray-500 italic">Signature</p>
                    <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-32 h-px bg-gray-400"></div>
                </div>
                <p class="text-sm text-gray-500 border-t-2 border-gray-300 pt-2">Directeur SEMOA</p>
            </div>
        </div>

        <!-- Instructions d'impression -->
        <div class="mt-8 p-4 bg-blue-50 rounded-lg text-center no-print">
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Imprimer ce reçu</button>
            <p class="text-sm text-gray-600 mt-2">Pour une meilleure qualité, imprimez ce reçu en format A4</p>
        </div>
    </div>

   <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Générer un numéro de reçu unique
        function generateReceiptNumber() {
            const now = new Date();
            const datePart = `${now.getFullYear()}${String(now.getMonth()+1).padStart(2,'0')}${String(now.getDate()).padStart(2,'0')}`;
            const randomPart = Math.floor(Math.random() * 900) + 100;
            return `${datePart}-${randomPart}`;
        }

        // Formater la date
        function formatDate(date) {
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        // Formater la date et heure
        function formatDateTime(date) {
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${day}/${month}/${year} ${hours}:${minutes}`;
        }

        // Générer des données de paiement (à remplacer par les données réelles)
        function generatePaymentData() {
            const now = new Date();
            
            return {
                receiptNumber: `SEMOA-${generateReceiptNumber()}`,
                receiptDate: formatDate(now),
                studentName: "Jean Dupont",
                studentId: "ETU" + Math.floor(Math.random() * 900000 + 100000),
                school: "Université de Yaoundé I",
                program: "Informatique",
                tuitionFee: "50 000 FCFA",
                registrationFee: "10 000 FCFA",
                libraryFee: "5 000 FCFA",
                totalAmount: "65 000 FCFA",
                paymentRef: `PAY-${now.getFullYear()}${String(now.getMonth()+1).padStart(2,'0')}${String(now.getDate()).padStart(2,'0')}-${Math.floor(Math.random()*9000)+1000}`,
                paymentMethod: ["Mobile Money (MTN)", "Orange Money", "Carte Bancaire"][Math.floor(Math.random()*3)],
                transactionId: `TRX${Math.floor(Math.random()*900000000)+100000000}`,
                transactionDate: formatDateTime(now)
            };
        }

        // Remplir les données du reçu
        function populateReceipt() {
            const paymentData = generatePaymentData();
            
            // Éléments à remplir
            const elements = {
                receiptNumber: paymentData.receiptNumber,
                receiptDate: paymentData.receiptDate,
                studentName: paymentData.studentName,
                studentId: paymentData.studentId,
                school: paymentData.school,
                program: paymentData.program,
                tuitionFee: paymentData.tuitionFee,
                registrationFee: paymentData.registrationFee,
                libraryFee: paymentData.libraryFee,
                totalAmount: paymentData.totalAmount,
                paymentRef: paymentData.paymentRef,
                paymentMethod: paymentData.paymentMethod,
                transactionId: paymentData.transactionId,
                transactionDate: paymentData.transactionDate
            };

            // Remplir chaque élément dans le DOM
            for (const [id, value] of Object.entries(elements)) {
                document.getElementById(id).textContent = value;
            }
        }

        // Initialiser le reçu
        populateReceipt();

        // Ajouter un bouton pour régénérer les données (démonstration seulement)
        const demoBtn = document.createElement('button');
        demoBtn.textContent = 'Générer un nouveau reçu (démo)';
        demoBtn.className = 'mt-4 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 no-print';
        demoBtn.onclick = populateReceipt;
        document.querySelector('.no-print').appendChild(demoBtn);

        // Option pour télécharger en PDF
        const pdfBtn = document.createElement('button');
        pdfBtn.textContent = 'Télécharger en PDF';
        pdfBtn.className = 'ml-2 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 no-print';
        pdfBtn.onclick = function() {
            window.print(); // Dans un vrai projet, utiliser une librairie comme jsPDF
        };
        document.querySelector('.no-print').appendChild(pdfBtn);
    });
</script>
