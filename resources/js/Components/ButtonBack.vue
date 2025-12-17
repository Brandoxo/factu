<template>
    <button class="button-back"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
        
    </svg></button>
</template>
    <style>
      .button-back {
            animation: slideInBack 1s ease-in-out;
        }
    
        @keyframes slideInBack {
            0% {
                opacity: 0;
                transform: translateY(-100%);
            }
    
            25% {
                opacity: 0.5;
            }
    
            50% {
                opacity: 1;
            }
    
            100% {
                opacity: 1;
                transform: translateY(0);
            }
    
            50% {
                opacity: 1;
            }
    
            100% {
                opacity: 1;
            }
        }
    </style>