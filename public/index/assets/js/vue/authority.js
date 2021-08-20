!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                
            }
        },
        components: {
            'i-footer'    : inisTemp.footer(),
            'i-top-nav'   : inisTemp.navbar(),
            'i-left-side' : inisTemp.sidebar(),
            'i-right-side': inisTemp.sidebar('right'),
        },
        mounted() {
            
        },
        methods: {
            
        }
    }).mount('#authority')

}(window.jQuery);