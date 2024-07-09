<template>
    <div>
        <div class="backdrop" @click="close()">
        </div>
        <div class="modal">
            <div v-if="!loading" class="modal-header">
                <transition name="fade">
                    <section class="flexContent">
                        <div class="orderDetails">
                            <img src="../../../images/iconPaper.png" />
                            <ul>
                                <li><span>Order #: </span>{{ orderID }}</li>
                                <li><span>Name: </span>{{ details.order.Name }}</li>
                                <li><span>Surname: </span>{{ details.order.Surname }}</li>
                                <li><span>Status: </span>{{ orderStatus }}</li>
                                <li v-if="details.order.TrackingCode != '' && details.order.TrackingCode != null">
                                    <span>Tracking Code: </span>{{ details.order.TrackingCode }}
                                </li>
                                <!-- <li v-if="details.order.Repeats != '0' && details.order.Repeats != '' && [143, 162, 205, 243].includes(details.order.DCountryCode)">
                                    <span>Commercial value: </span>{{ details.order.Repeats }}
                                </li> -->
                            </ul>
                        </div>
                    </section>
                </transition>

                <transition name="fade">
                    <section v-if="products.length != 0" class="flexContent">
                        <div class="productListItem mb-10" v-for="p in products">
                            <div class="title">
                                <h3>{{ p.Name }}, {{ p.Dosage }} x {{ p.Quantity }} {{ p.Unit }}</h3>
                            </div>
                        </div>
                    </section>
                </transition>
            </div>
            <transition name="fade">
                <form
                    v-if="countries.length != 0 && companies.length != 0 && !loading && Object.keys(confirmationChanges).length == 0 && Object.keys(confirmationChangesUPS).length == 0"
                    @submit.prevent="save" style="align-items: flex-start;justify-content: flex-start;display: flex;"
                    class="pxp-form mb-20">
                    <div class="form-column">
                        <h3>Home Details</h3>
                        <div v-if="columnHome.includes(value)" class="form-group form-group-2"
                            v-for="(key, value) in details.order" :key="value">
                            <label :for="key">{{ alias[value] }}</label>
                            <input v-if="!['CountryCode', 'DCountryCode', 'DeliveryID', 'Notes'].includes(value)"
                                class="" :name="key" :placeholder="''" v-model="details.order[value]">
                            <select v-else-if="['DCountryCode', 'CountryCode'].includes(value)"
                                v-model="details.order[value]">
                                <option v-for="country in countries" :value="country.CountryID">{{ country.Name }}
                                </option>
                            </select>
                            <select v-else-if="['DeliveryID'].includes(value)" v-model="details.order[value]">
                                <option v-for="company in companies" :value="company.SettingID">{{ company.Name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-column">
                        <h3>Delivery Details</h3>
                        <div v-if="columnDelivery.includes(value)" class="form-group form-group-2"
                            v-for="(key, value) in details.order" :key="value">
                            <label :for="key">{{ alias[value] }}</label>
                            <input v-if="!['CountryCode', 'DCountryCode', 'DeliveryID', 'Notes'].includes(value)"
                                class="" :name="key" :placeholder="''" v-model="details.order[value]">
                            <select v-else-if="['DCountryCode', 'CountryCode'].includes(value)"
                                v-model="details.order[value]">
                                <option v-for="country in countries" :value="country.CountryID">{{ country.Name }}
                                </option>
                            </select>
                            <select v-else-if="['DeliveryID'].includes(value)" v-model="details.order[value]">
                                <option v-for="company in companies" :value="company.SettingID">{{ company.Name }}
                                </option>
                            </select>
                        </div>

                        <!-- <div class="form-group form-group-2">
                            <label for="Notes">{{alias['Notes']}}</label>
                            <textarea style="min-width: 300px; min-height: 60px; line-height: 1;" placeholder="Add notes here if you want them to show for dispensers and customer support"
                            v-model="details.order.Notes" class="form-control tBoxSize02"></textarea>
                        </div>                    -->
                    </div>

                    <!-- <div class="form-column">
                        <h3 v-if="details.ups != null">UPS Access Point</h3>
                        <div class="form-group" v-for="(key, value) in details.ups" :key="value">
                            <label :for="key">{{alias[value]}}</label>
                            <input v-if="!['CountryCode', 'DCountryCode'].includes(value)" class="" :name="key" :placeholder="''" v-model="details.ups[value]">
                            <select v-else-if="['DCountryCode', 'CountryCode'].includes(value)" v-model="details.ups[value]">
                                <option v-for="country in countries" :value="country.CountryID">{{country.Name}}</option>
                            </select>
                        </div>
                    </div> -->
                </form>
            </transition>

            <transition name="fade">
                <div v-if="(Object.keys(confirmationChanges).length > 0 || Object.keys(confirmationChangesUPS).length > 0) && !loading"
                    class="pxp-form mb-20">
                    <div class="infoBox warning">
                        <p>Please review and confirm all the changes in the order before saving!</p>
                    </div>

                    <DiffTableAddress :old-object="confirmationOld" :new-object="confirmationChanges"
                        :old-object-u-p-s="confirmationOldUPS" :new-object-u-p-s="confirmationChangesUPS"
                        :countries-prop="countries" :companies-prop="companies" />
                </div>
            </transition>

            <transition name="fade">
                <div v-if="!loading" class="modal-footer">
                    <button v-if="!isEqual(details.order, details.oldOrder) || !isEqual(details.ups, details.oldUPS)"
                        @click="save()" class="btn btnSize01 tertiaryBtn bigButton big-square-button">
                        <span v-if="!saveConfirmation">
                            Review
                        </span>
                        <span v-else>
                            Save
                        </span>
                    </button>

                    <button
                        v-if="(!isEqual(details.order, details.oldOrder) || !isEqual(details.ups, details.oldUPS)) && saveConfirmation"
                        @click="back()" class="btn btnSize01 tertiaryBtn bigButton big-square-button">
                        <span>
                            Back
                        </span>
                    </button>

                    <button @click="close()" class="btn btnSize01 secondaryBtn bigButton">Cancel</button>
                </div>
            </transition>
            <div v-if="loading" class="loader" style="">Loading...</div>
            <span class="close" @click="close()">X</span>
        </div>
    </div>
</template>

<script>
import _ from 'lodash';
import Error from '../../mixins/errors';
import PrescriptionColumns from '../../mixins/constants/prescriptionColumns';
import DiffTableAddress from '../pages/DiffTableAddress.vue';

export default {
    mixins: [Error, PrescriptionColumns],
    props: ['orderID', 'orderStatus', 'products'],
    components: { DiffTableAddress },
    data: function () {
        return {
            countries: [],
            companies: [],
            details: {
                order: {},
                oldOrder: {},
                ups: {},
                oldUps: {},
            },
            columnDelivery: ['Repeats', 'DAddress1', 'DAddress2', 'DAddress3', 'DAddress4', 'DPostcode', 'DCountryCode', 'TrackingCode'],
            columnHome: ['Name', 'Surname', 'Address1', 'Address2', 'Address3', 'Address4', 'Postcode', 'CountryCode', 'Telephone', 'Email', /*'TokenID',*/ 'APNotificationLanguage', 'APNotificationValue'],
            loading: true,
            //save confirmation
            saveConfirmation: false,
            confirmationChanges: {},
            confirmationChangesUPS: {},
            confirmationOld: {},
            confirmationOldUPS: {},
        }
    },
    computed: {

    },
    mounted() {
        this.getCountries();
        this.getCompanies();
        this.getOrderDetails();
        this.emitter.on('modal.close.all', this.close);
    },
    destroyed() {
        this.emitter.off('modal.close.all');
    },
    methods: {
        getOrderDetails() {
            axios.get('/order-edit/' + this.orderID)
                .then((response) => {
                    this.details = response.data.data;
                    this.loading = false;
                })
                .catch((error) => {
                    console.warn(error.response.data.message);
                    this.loading = false;
                })
        },
        getCountries() {
            axios.get('/countries')
                .then((response) => {
                    this.countries = response.data.data;
                })
                .catch((error) => {
                    console.warn(error.response.data.message);
                })
        },
        getCompanies() {
            axios.get('/delivery-companies')
                .then((response) => {
                    this.companies = response.data.data;
                })
                .catch((error) => {
                    console.warn(error.response.data.message);
                })
        },
        /**
         * Fetches country title by country id
         */
        getCountryTitle(id, countries = false) {
            let title = 'Unknown';

            if (!countries) {
                countries = this.countries;
            }

            countries.forEach((country) => {
                if (country.CountryID == id) {
                    title = country.Name;
                }
            })

            return title;
        },
        /**
         * Fetches company title by SettingID id
         */
        getCompanyTitle(id, companies = false) {
            let title = 'Unknown';

            if (!companies) {
                companies = this.companies;
            }

            companies.forEach((company) => {
                if (company.SettingID == id) {
                    title = company.Name;
                }
            })

            return title;
        },
        close() {
            this.saveConfirmation = false;
            this.confirmationChanges = {};
            this.confirmationOld = {};
            this.confirmationOld = {};
            this.confirmationOldUPS = {};
            this.details = { order: {}, oldOrder: {}, ups: {}, oldUps: {} };//clean up after
            this.$emit('closeorder');
        },
        save() {
            if (this.saveConfirmation) {
                this.submit();
            } else {
                axios.post(`/order-edit/check/${this.orderID}`, { order: this.details.order, ups: this.details.ups })
                    .then((response) => {
                        if (Object.keys(response.data.data.changes).length > 0 || Object.keys(response.data.data.changesUPS).length) {
                            this.confirmationChanges = response.data.data.changes;
                            this.confirmationChangesUPS = response.data.data.changesUPS;
                            this.confirmationOld = response.data.data.old;
                            this.confirmationOldUPS = response.data.data.oldUPS;
                            this.saveConfirmation = true;
                        } else {
                            this.saveConfirmation = false;
                            this.$emit('closeorder');
                        }
                    })
                    .catch((error) => {
                        this.saveConfirmation = false;
                        this.postError(error);
                    })
            }
        },
        submit() {
            axios.post('/order-edit/' + this.orderID, { order: this.details.order, ups: this.details.ups })
                .then((response) => {
                    this.postSuccess('Saved');
                    this.close();
                    this.saveConfirmation = false;
                    this.emitter.emit('orderupdate');
                })
                .catch((error) => {
                    this.postError(error);
                    this.close();
                    this.saveConfirmation = false;
                    this.emitter.emit('orderupdate');
                })
        },
        back() {
            this.saveConfirmation = false;
            this.confirmationChanges = {};
            this.confirmationChangesUPS = {};
        },
        isEqual: _.isEqual,
    }
}
</script>
