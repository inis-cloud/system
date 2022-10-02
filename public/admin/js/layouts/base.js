const utils   = inisHelper
window.onload = () => {
    window.notyf = new Notyf
}

const GET  = (url, params, config) => utils.fetch.get( url, params, config)
const POST = (url, params, config) => utils.fetch.post(url, params, config)

// 公共方法
const Tool = {
    // 提示框
    // type  => default success error|danger warning custom
    // event => click | dismiss
    Notyf: (message = 'hello', type = 'default', config = {}) => {
        const color = {'default':'#1e90ff', 'success':'#28a745', 'error':'#dc3545', 'danger':'#dc3545', 'warning':'#ffc107', 'custom':'orange'}
        const icon  = {'default':'fas fa-info-circle', 'success':'fas fa-check-circle', 'error':'fas fa-times-circle', 'danger':'fas fa-times-circle', 'warning':'fas fa-exclamation-circle', 'custom':'fas fa-info-circle'}
        config = {
            type, message,
            ripple: true,
            dismissible: true,
            duration: 2 * 1000,
            position: {
                x: 'right',
                y: 'top'
            },
            ...config,
            // background: '#1e90ff',
            // icon: {
            //     className: 'material-icons',
            //     tagName: 'i',
            //     text: 'warning'
            // }
        }
        config.background = color[type]
        // config.icon = {
        //     className: icon[type],
        //     tagName: 'i',
        //     text: 'warning'
        // }

        // 有可能执行太快了，所以手动修复一下
        try {

            return notyf.open(config)

        } catch {

            window.runtime = setInterval(()=>{
                window.notyf = new Notyf
                if (window.notyf) {
                    clearInterval(window.runtime)
                    return notyf.open(config)
                }
            }, 500)
        }

        // return notyf.open(config)
        // .on('dismiss', ({target, event}) => {
        //     console.log(target)
        //     console.log(event)
        // });
    }
}