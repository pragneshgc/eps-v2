<template>
    <div class="content">
        <section class="card">
            <div class="card-header">
                <h3>New Pharmacy</h3>
            </div>
            <!-- Grid Row -->
            <div class="card-body">
                <form class="text-center p-5" v-on:submit.prevent="save">
                    <div class="row mb-3">
                        <div class="col-lg-6 mb-10">
                            <!-- Name -->
                            <input valid autocomplete="off" v-model="data.Title" type="text" id="defaultContactFormTitle" class="form-control tBoxSize02" placeholder="Title">
                            <div v-if="errors.Title" class="invalid-feedback d-block">{{ errors.Title[0] }}</div>
                        </div>
                        <!-- Location -->
                        <!-- <div class="col-lg-6 mb-10">
                            <input valid autocomplete="off" v-model="data.Location" type="text" id="defaultContactFormLocation" class="form-control tBoxSize02" placeholder="Location">
                            <div v-if="errors.Location" class="invalid-feedback d-block">{{ errors.Location[0] }}</div>
                        </div> -->
                    </div>
                    <!-- Send button -->
                    <button class="btn btnSize01 secondaryBtn" type="submit">Save</button>
                </form>
            </div>
            <!--Grid row-->                    
        </section>
    </div>
</template>

<script>
    import Error from '../../../mixins/errors'

    export default {
        mixins: [ Error ],
        data: function () {
            return {
                data: {
                    Title: '',
                    Location: '',
                },
                loading: false,
                errors: {},
                userInfo: userInfo,
            }
        },
        mounted() {
        },
        computed: {
            postUrl: function(){
                return '/pharmacies';
            }
        },
        methods: {
            save: function(){
                this.loading = true;

                axios.put(this.postUrl, this.data)
                .then((response) => {
                    this.postSuccess(response.data.message);
                    this.errors = {};
                    this.loading = false;
                    this.$router.push('/pharmacies');
                })
                .catch((error) => {
                    this.errors = error.response.data.errors;
                    this.loading = false;
                });
            },
        }
    }
</script>
