<!-- ====== Footer Section Start -->
<footer class="relative z-10 bg-[#fbef8b] text-black pt-20 pb-10 lg:pt-[120px] lg:pb-4 border-t-8 border-[#b09d72]">
  <div class="container mx-auto px-4">
    <!-- Grid container -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 text-center lg:text-left">
      
      <!-- Logo + description -->
      <div class="lg:col-span-2 flex flex-col items-center lg:items-start max-w-[280px] mx-auto lg:mx-0">
        <a href="/" class="mb-6 inline-block">
          <img src="https://www.iai-togo.tg/wp-content/uploads/2017/06/logo.jpeg" alt="logo" class="w-[160px] mx-auto lg:mx-0">
        </a>
        <p class="mb-7 text-base leading-relaxed">
          L'Institut Africain d'Informatique (IAI) et son réseau sont des centres de référence en matière de formation en Afrique.
        </p>
      </div>

      <!-- Contact -->
      <div class="flex flex-col items-center lg:items-start">
        <h4 class="mb-4 text-lg font-semibold uppercase">Contact</h4>
        <ul class="space-y-4">
          <li>
            <a href="mailto:iaitogo@iai-togo.tg" class="flex items-center justify-center lg:justify-start">
              <i class="fas fa-envelope text-xl mr-3"></i>
              <span>iaitogo@iai-togo.tg</span>
            </a>
          </li>
          <li>
            <a href="tel:+22822204700" class="flex items-center justify-center lg:justify-start">
              <i class="fas fa-phone-alt text-xl mr-3"></i>
              <span>(00228) 22 20 47 00</span>
            </a>
          </li>
          <li class="flex items-start justify-center lg:justify-start">
            <i class="fas fa-map-marker-alt text-xl mr-3 mt-1"></i>
            <span>59 rue de la Kozah Nyékonakpoè <br> 07 BP:12456 Lomé 07, Togo</span>
          </li>
        </ul>
      </div>

      <!-- A Propos -->
      <div class="flex flex-col items-center lg:items-start">
        <h4 class="mb-4 text-lg font-semibold uppercase">À propos</h4>
        <ul class="space-y-2">
          <li><a href="#" class="hover:underline">Newsletter</a></li>
          <li><a href="{{ route('contact') }}" class="hover:underline">Contact & Support</a></li>
          <li><a href="#" class="hover:underline">Évènements</a></li>
          <li><a href="{{ route('cgu') }}" class="hover:underline">Conditions générales</a></li>
        </ul>
      </div>

      <!-- Liens rapides -->
      <div class="flex flex-col items-center lg:items-start">
        <h4 class="mb-4 text-lg font-semibold uppercase">Liens rapides</h4>
        <ul class="space-y-2">
          <li><a href="{{ route('candidatures.create') }}" class="hover:underline">Inscription</a></li>
          <li><a href="{{ route('formations') }}" class="hover:underline">Formations</a></li>
          <li><a href="#" class="hover:underline">Nos partenaires</a></li>
          <li><a href="#" class="hover:underline">FAQ</a></li>
          <li><a href="#" class="hover:underline">Concours d'entrée</a></li>
          <li><a href="#" class="hover:underline">Résultat du concours</a></li>
        </ul>
      </div>
    </div>

    <!-- Séparateur vert -->
    <div class="border-t-4 border-green-600 mt-10 mb-6"></div>

    <!-- Footer bottom -->
    <div class="flex flex-col items-center space-y-4">
      <!-- Réseaux sociaux -->
      <div class="flex space-x-5">
        <a href="https://www.facebook.com/share/LgvDBpJZeQDv4kGf/?mibextid=qi2Omg" target="_blank" class="hover:text-blue-700">
          <i class="fab fa-facebook-f text-xl"></i>
        </a>
        <a href="https://x.com/iaitogofficiel" target="_blank" class="hover:text-sky-500">
          <i class="fab fa-twitter text-xl"></i>
        </a>
        <a href="https://www.linkedin.com/company/institut-africain-d-informatique" target="_blank" class="hover:text-blue-600">
          <i class="fab fa-linkedin-in text-xl"></i>
        </a>
      </div>

      <!-- Copyright -->
       <div class="text-center text-sm text-black">
      © 2025 <span class="text-green-700 font-semibold">IAI Togo</span>. Tous droits réservés. <a href="https://neostart.tech/" target="_blank" class="text-green-700 font-semibold">Développer par Neo Start Technology</a>
    </div>
    </div>
  </div>
</footer>
<!-- ====== Footer Section End -->
