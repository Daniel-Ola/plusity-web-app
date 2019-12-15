<template>
    <div>
        <TheHeader />
        <main class="py-4">
            <router-view></router-view>
        </main>
        <ThePrimaryFooter />
    </div>
</template>

<script>
import TheHeader from './views/shared/Nav/Header/TheHeader.vue'
import ThePrimaryFooter from './views/shared/Footer/ThePrimaryFooter.vue'

    export default {
        name: 'App',
        mounted() {
            console.log('Component mounted.')
        },
        components: {
            TheHeader,
            ThePrimaryFooter,
        }
    }
</script>
