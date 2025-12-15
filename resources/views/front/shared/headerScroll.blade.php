<nav class="z-30 to-salmon/50 from-hielow/80 bg-linear-to-l top-0 left-0 w-full transition-all duration-800 fixed opacity-0" id="scrollHeader">

    <!-- {{-- Menú desktop --}} -->

    <div class="md:flex hidden justify-between items-center px-8 md:px-20 lg:px-40 2xl:px-60 py-2 md:py-1 mx-auto z-30"
    data-aos="fade-down"
data-aos-duration="100"
data-aos-once="false">
            <a class="" href="#">
              <img class="h-14" src="{{asset('assets/img/logos/logo.png')}}" alt="Logo Hotel FCH Hotel Navbar">
            </a>
            <ul class="text-white ml-auto md:flex gap-4 font-semibold 2xl:text-xl">
                <li><a href="/"><p>Hoteles</p> <h1 class="active1 hidden pb-2 text-center relative lg:text-center after:content-[''] after:absolute after:buttom-0 lg-after-left-1/2 after:-translate-x-1/2 after:w-20 after:h-1 after:bg-salmon group-hover:block"></h1></li>
                <li><a href="/desing-and-art"><p>Arte y Diseño</p> <h1 class="active2 hidden pb-2 text-center relative lg:text-center after:content-[''] after:absolute after:buttom-0 lg-after-left-1/2 after:-translate-x-1/2 after:w-20 after:h-1 after:bg-aqua group-hover:block"></h1></li>
                <li><a href="/restaurant-ananas"><p>Gourmet</p> <h1 class="active3 hidden pb-2 text-center relative lg:text-center after:content-[''] after:absolute after:buttom-0 lg-after-left-1/2 after:-translate-x-1/2 after:w-20 after:h-1 after:bg-hielow group-hover:block"></h1></li>
                <li><a href="/contact"><p>Contacto</p> <h1 class="active4 hidden pb-2 text-center relative lg:text-center after:content-[''] after:absolute after:buttom-0 lg-after-left-1/2 after:-translate-x-1/2 after:w-20 after:h-1 after:bg-blue group-hover:block"></h1></li>
                <li><a href="#"><p>EN</p> <h1 class="active5 hidden pb-2 text-center relative lg:text-center after:content-[''] after:absolute after:buttom-0 lg-after-left-1/2 after:-translate-x-1/2 after:w-20 after:h-1 after:bg-salmon group-hover:block"></h1></li>
            </ul>

    </div>

    <!-- {{-- Menú mobile --}} -->
    
    <div class="flex justify-between p-2">

        <div class="md:hidden block open-nav-btn" >
    <img class="w-12" src="{{ asset('assets/icons/menu.svg') }}" alt="menu">
</div>
        
        <div class="md:hidden align-center items-center">
            <a  href="/">
                <img class="w-8" src="{{ asset('assets/img/logos/logoColor.png') }}" alt="menu">
            </a>
        </div>
       
    </div>
</nav>