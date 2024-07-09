export default {
    methods: {
        /**
         * Generic error report
         */
        reportError: function (error) {
            let audio = new Audio('/audio/alarm.mp3');
            audio.play();

            this.$swal({
                position: 'bottom',
                type: 'error',
                title: 'Error fetching data!',
                showConfirmButton: false,
                timer: 5000,
                toast: true,
                text: 'Try refreshing your page, we will notify the developers.',
            }).then(() => {
                console.warn(error.response);
            });
        },
        postError: function (error) {
            let audio = new Audio('/audio/alarm.mp3');
            audio.play();

            this.$swal({
                position: 'bottom',
                type: 'error',
                title: 'Whoops!',
                showConfirmButton: false,
                timer: 5000,
                toast: true,
                text: error,
            }).then(() => {
                console.warn(error);
            });
        },
        postErrorPopup: function (error) {
            let audio = new Audio('/audio/alarm.mp3');
            audio.play();

            this.$swal({
                type: 'error',
                title: 'Whoops!',
                showConfirmButton: false,
                html: error,
                showConfirmButton: true,
            }).then((result) => {
                console.warn(result);
            });
        },
        postSuccess: function (response) {
            this.$swal({
                position: 'bottom',
                type: 'success',
                title: 'Success!',
                showConfirmButton: false,
                timer: 5000,
                //timer: 9999999999999999,
                toast: true,
                text: response,
            });
        },

        /**
         * TOASTS
         */
        reportErrorToast: function (error) {
            let audio = new Audio('/audio/alarm.mp3');
            audio.play();

            this.$toasted.show(error, 
                {
                    type : 'error',
                    iconPack: 'fontawesome',
                    icon : 'exclamation',                    
                    duration : 5000,
                    position: "top-right",
                    action : {
                        text : 'Dismiss',
                        onClick : (e, toastObject) => {
                            toastObject.goAway(0);
                        }
                    },
                }
            )
        },
        postErrorToast: function (error) {
            let audio = new Audio('/audio/alarm.mp3');
            audio.play();

            this.$toasted.show(error, 
                {
                    type : 'error',
                    iconPack: 'fontawesome',
                    icon : 'exclamation',
                    duration : 5000,
                    position: "top-right",
                    action : {
                        text : 'Dismiss',
                        onClick : (e, toastObject) => {
                            toastObject.goAway(0);
                        }
                    },
                }
            )
        },
        postErrorToast: function (error) {
            let audio = new Audio('/audio/alarm.mp3');
            audio.play();

            this.$swal({
                type: 'error',
                title: 'Whoops!',
                showConfirmButton: false,
                html: error,
                showConfirmButton: true,
            }).then((result) => {
                console.warn(result);
            });
        },
        postSuccessToast: function (response) {
            if(response == ''){
                response = 'Success!'
            }  

            this.$toasted.show(response,
                {
                    iconPack: 'fontawesome',
                    type : 'success',
                    icon : 'check',
                    duration : 2000,
                    position: "top-right",
                    action : {
                        text : 'Dismiss',
                        onClick : (e, toastObject) => {
                            toastObject.goAway(0);
                        }
                    },
                }
            )
        },
    }
}