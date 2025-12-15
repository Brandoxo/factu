<!-- <nav>
    <div class="flex justify-between items-center px-8 py-2 md:py-1 bg-red-200 h-[60px] border-b-2 border-b-salmon max-w-screen-2xl mx-auto">
        <div class="flex items-center gap-4">
            <div class="hidden md:inline cursor-pointer" data-open-nav id="openNavBtn">
                <img class="w-10" src="{{ asset('assets/icons/menu.svg') }}" alt="menu">
            </div>
            <a  href="#">
                <img class="h-14" src="{{asset('assets/img/logos/logo-hor.png')}}" alt="Logo Hotel FCH Hotel Navbar">
            </a>
        </div>
        <div class="md:hidden block" data-open-nav id="openNavBtnMovile">
            <img src="{{ asset('assets/icons/menu.svg') }}" alt="menu">
        </div>
        <div class="hidden md:block bg-salmon rounded-xl">
            <a target="_blank" href="https://wa.me/3333113958?text=Hola%20me%20gustaría%20saber%20más%20sobre%20el%20hotel" class="bg-salmon h-12 w-12 p-1 rounded-xl block">
                <img class="w-full h-full" src="{{ asset('assets/icons/whatsapp.svg') }}" alt="menu">
            </a>
        </div>
    </div>
</nav> --> 
<nav class="z-30 bg-transparent top-0 left-0 w-full transition-all duration-300 absolute" id="mainHeader">
    <div class="md:flex hidden justify-between items-center px-8 md:px-20 lg:px-40 2xl:px-60 py-2 md:py-1 mx-auto z-30">
            <a class="" href="#">
              <img class="h-14" src="{{asset('assets/img/logos/logo.svg')}}" alt="Logo Holtel Ronda Minerva">
            </a>
            <ul class="text-white ml-auto md:flex gap-4 font-semibold 2xl:text-xl"><li><a href="#"><p>Sistema de Facturación</p> <h1 class="active5 hidden pb-2 text-center relative lg:text-center after:content-[''] after:absolute after:buttom-0 lg-after-left-1/2 after:-translate-x-1/2 after:w-20 after:h-1 after:bg-salmon group-hover:block"></h1></li>
            </ul>

    </div>

    <div class="flex justify-between p-2">

        <div class="md:hidden block open-nav-btn">
            <img class="w-12" src="{{ asset('assets/icons/menu.svg') }}" alt="menu">
        </div>
        
        <div class="md:hidden align-center items-center">
            <a  href="/">
                <img class="w-8" src="{{ asset('assets/img/logos/logoColor.png') }}" alt="menu">
            </a>
        </div>
       
    </div>
</nav>
