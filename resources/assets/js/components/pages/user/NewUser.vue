<template>
    <div class="content">
        <section class="card">
            <div class="card-header">
                <h3>New User</h3>
            </div>
            <!-- Grid Row -->
            <div class="card-body">
                <form class="text-center p-5" v-on:submit.prevent="save">
                    <div class="row mb-3">
                        <div class="col-lg-6 mb-10">
                            <!-- Name -->
                            <input valid autocomplete="off" v-model="data.name" type="text" id="defaultContactFormName"
                                class="form-control tBoxSize02" placeholder="Name">
                            <div v-if="errors.name" class="invalid-feedback d-block">{{ errors.name[0] }}</div>
                        </div>
                        <div class="col-lg-6 mb-10">
                            <!-- Surname -->
                            <input autocomplete="off" v-model="data.surname" type="text" id="defaultContactFormSurnname"
                                class="form-control tBoxSize02" placeholder="Surname">
                            <div v-if="errors.surname" class="invalid-feedback d-block">{{ errors.surname[0] }}</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-6 mb-10">
                            <!-- Email -->
                            <input autocomplete="off" v-model="data.email" type="email" id="defaultContactFormEmail"
                                class="form-control tBoxSize02" placeholder="E-mail">
                            <div v-if="errors.email" class="invalid-feedback d-block">{{ errors.email[0] }}</div>
                        </div>
                        <div class="col-lg-6 mb-10">
                            <!-- Role -->
                            <select v-model="data.role" class="browser-default custom-select">
                                <option value="5">Shipping</option>
                                <option v-if="userInfo.role >= 30" value="30">Pharmacy Admin</option>
                                <option v-if="userInfo.role >= 50" value="50">Admin</option>
                                <option v-if="userInfo.role >= 60" value="60">SysAdmin</option>
                            </select>
                            <div v-if="errors.role" class="invalid-feedback d-block">{{ errors.role[0] }}</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-6 mb-10">
                            <input autocomplete="off" v-model="data.password" type="password" name="password" id="password"
                                class="form-control tBoxSize02" placeholder="Password">
                            <div v-if="errors.password" class="invalid-feedback d-block">{{ errors.password[0] }}</div>
                        </div>
                        <div class="col-lg-6 mb-10">
                            <input autocomplete="off" v-model="data.passwordRepeat" type="password" name="password-repeat"
                                id="passwordRepeat" class="form-control tBoxSize02" placeholder="Repeat Password">
                            <div v-if="errors.passwordRepeat" class="invalid-feedback d-block">{{ errors.passwordRepeat[0]
                            }}</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-12 mb-10">
                            <select v-model="data.pharmacy_id" class="browser-default custom-select mb-10">
                                <option selected disabled>Select Pharmacy</option>
                                <option v-for="pharmacy in pharmacies" :key="pharmacy.PharmacyID"
                                    :value="pharmacy.PharmacyID">{{ pharmacy.Title }}</option>
                            </select>
                        </div>
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
    mixins: [Error],
    data: function () {
        return {
            data: {
                name: '',
                surname: '',
                email: '',
                role: 5,
                password: '',
                passwordRepeat: '',
                pharmacy_id: null,
            },
            pharmacies: [],
            loading: false,
            errors: {},
            userInfo: userInfo,
        }
    },
    mounted() {
        this.getPharmacies();
    },
    computed: {
        postUrl: function () {
            return '/users';
        }
    },
    methods: {
        save: function () {
            this.loading = true;

            axios.put(this.postUrl, this.data)
                .then((response) => {
                    this.postSuccess(response.data.message);
                    this.errors = {};
                    this.loading = false;
                    this.$router.push('/users');
                })
                .catch((error) => {
                    this.errors = error.response.data.errors;
                    this.loading = false;
                });
        },
        getPharmacies() {
            axios.get('/pharmacies/list')
                .then((response) => {
                    this.pharmacies = response.data.data;
                })
                .catch((error) => {
                    this.reportError(error);
                })
        },
    }
}
</script>
