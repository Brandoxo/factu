<section id="hero-carousel" class="swiper w-full h-screen  z-10">
    <div class=" hero-bg bg-cover w-full h-screen relative">
        


            <div class="dark-overlay absolute -z-10 top-0 left-0 h-full w-full overflow-hidden" data-aos="fade-up">
                    <img class="w-full h-full bg-cover object-cover" src="{{ asset('/assets/img/bg/bg-main.webp') }}" alt="Hero Background Image" >
            </div>
            
            <div class="text-white text-center flex flex-col justify-center gap-3 h-full logo absolute top-1/4 left-1/2 transform -translate-x-1/2 -translate-y-1/2" >
                <div class="mx-auto">
                    <img class="w-60" src="{{ asset('assets/img/logos/logo-white.png') }}" alt="Logo Hotel FCH Minerva">
                </div>
            </div>

            <div class="text-white text-center flex flex-col justify-center items-center gap-6 h-full  absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                <h1 class="title text-2xl font-bold">¿Qué servicio desea facturar?</h1>
                <div class="w-fit flex flex-col gap-6 text-center justify-center items-center md:flex-row text-xl font-extrabold">
                    <button class="button-left p-4 font-semibold px-10 bg-red-600 w-86 rounded-md hover:">Hotel</button>
                    <button class="button-right p-4 font-semibold px-6 bg-red-600 w-96 rounded-md">Restaurante</button>
                </div>
            </div>
    </div>

</section>

<style>
    .logo{
        animation: fadeInDown 2s ease-in-out;
    }
    @keyframes fadeInDown {
        0% {
            opacity: 0;
            transform: translateY(50px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .button-left {
        animation: slideInLeft 1s ease-in-out;
    }

    @keyframes slideInLeft {
        0% {
            opacity: 0;
            transform: translateX(-100%);
        }

        25% {
            opacity: 0.5;
        }

        50% {
            opacity: 1;
        }

        100% {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .button-right {
        animation: slideInRight 1s ease-in-out;
    }

    @keyframes slideInRight {
        0% {
            opacity: 0;
            transform: translateX(100%);
        }

        25% {
            opacity: 0.5;
        }

        50% {
            opacity: 1;
        }

        100% {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .title {
        animation: retraso 3s ease-in-out;
    }

    @keyframes retraso {
        0% {
            opacity: 0;
        }

        25% {
            opacity: 0.3;
        }

        50% {
            opacity: 0.6;
        }

        75% {
            opacity: 0.8;
        }

        100% {
            opacity: 1;
        }
    }
</style>