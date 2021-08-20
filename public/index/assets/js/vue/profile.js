!function (t) {
    
    const app = Vue.createApp({
        data() {
            return {
                login_account: {}
            }
        },
        components: {
            'i-footer'    : inisTemp.footer(),
            'i-top-nav'   : inisTemp.navbar(),
            'i-left-side' : inisTemp.sidebar(),
            'i-right-side': inisTemp.sidebar('right'),
        },
        mounted() {
            this.login_account = JSON.parse(inisHelper.get.cookie('login_account'))
        },
        methods: {
            
        }
    }).mount('#profile')

}(window.jQuery);