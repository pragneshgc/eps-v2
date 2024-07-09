<template>
    <div>
        <div class="backdrop" @click="close()">
        </div>
        <div class="modal">
            <div v-if="!loading" class="modal-header">
                <transition name="fade">
                    <section class="flexContent">
                        <div class="orderDetails">
                            <img src="images/iconPaper.png" />
                            <ul>
                                <li><span>Order #: </span>{{ orderID }}</li>
                                <li><span>Name: </span>{{ details.order.Name }}</li>
                                <li><span>Surname: </span>{{ details.order.Surname }}</li>
                                <li><span>Status: </span>{{ orderStatus }}</li>
                                <li v-if="details.order.TrackingCode != '' && details.order.TrackingCode != null">
                                    <span>Tracking Code: </span>{{ details.order.TrackingCode }}
                                </li>
                                <li
                                    v-if="details.order.Repeats != '0' && details.order.Repeats != '' && [143, 162, 205, 243].includes(details.order.DCountryCode)">
                                    <span>Commercial value: </span>{{ details.order.Repeats }}
                                </li>
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
                    v-if="countries.length != 0 && companies.length != 0 && !loading && Object.keys(confirmationChanges).length == 0"
                    @submit.prevent="save" class="pxp-form mb-20">
                    <div class="form-column">
                        <h3>Delivery Details</h3>
                        <div class="form-group form-group-column" v-for="(key, value) in details.order" :key="value">
                            <label :for="key">{{ alias[value] }}</label>
                            <input v-if="!['CountryCode', 'DCountryCode', 'DeliveryID'].includes(value)" class=""
                                :name="key" :placeholder="''" v-model="details.order[value]">
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
                        <h3>Home Details</h3>
                        <div class="form-group form-group-column" v-for="(key, value) in details.order" :key="value">
                            <label :for="key">{{ alias[value] }}</label>
                            <input v-if="!['CountryCode', 'DCountryCode', 'DeliveryID'].includes(value)" class=""
                                :name="key" :placeholder="''" v-model="details.order[value]">
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
                        <h3 v-if="details.ups != null">UPS Access Point</h3>
                        <div class="form-group form-group-column" v-for="(key, value) in details.ups" :key="value">
                            <label :for="key">{{ alias[value] }}</label>
                            <input class="" :name="key" :placeholder="''" v-model="details.ups[value]">
                        </div>
                    </div>
                </form>
            </transition>

            <transition name="fade">
                <div v-if="Object.keys(confirmationChanges).length > 0 && !loading" class="pxp-form mb-20">
                    <div class="infoBox warning">
                        <p>Please review and confirm all the changes in the order before saving!</p>
                    </div>

                    <table class="table table-hover mt-20">
                        <thead>
                            <tr>
                                <th>
                                    Field
                                </th>
                                <th>
                                    Old Value
                                </th>
                                <th>
                                    New Value
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(change, key) in confirmationChanges" :key="key">
                                <td>
                                    {{ alias[key] }}
                                </td>
                                <td>
                                    <span v-if="key == 'DeliveryID'">{{ getCompanyTitle(confirmationOld[key]) }}</span>
                                    <span v-else-if="key == 'DCountryCode'">{{ getCountryTitle(confirmationOld[key])
                                        }}</span>
                                    <span v-else-if="key == 'CountryCode'">{{ getCountryTitle(confirmationOld[key])
                                        }}</span>
                                    <span v-else>{{ confirmationOld[key] }}</span>
                                </td>
                                <td>
                                    <span v-if="key == 'DeliveryID'">{{ getCompanyTitle(confirmationChanges[key])
                                        }}</span>
                                    <span v-else-if="key == 'DCountryCode'">{{ getCountryTitle(confirmationChanges[key])
                                        }}</span>
                                    <span v-else-if="key == 'CountryCode'">{{ getCountryTitle(confirmationChanges[key])
                                        }}</span>
                                    <span v-else>{{ confirmationChanges[key] }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
                        v-else-if="(!isEqual(details.order, details.oldOrder) || !isEqual(details.ups, details.oldUPS)) && saveConfirmation"
                        @click="back()" class="btn btnSize01 tertiaryBtn bigButton big-square-button">
                        <span>
                            Back
                        </span>
                    </button>

                    <button @click="close()" class="btn btnSize01 tertiaryBtn bigButton cancel">Cancel</button>
                </div>
            </transition>
            <div v-if="loading" class="loader" style="">Loading...</div>
            <span class="close" @click="close()">X</span>
        </div>
    </div>
</template>

<script>
import _ from 'lodash';
import Error from '../../mixins/errors'

export default {
    props: ['orderID', 'orderStatus', 'products'],
    mixins: [Error],
    data: function () {
        return {
            countries: [],
            companies: [],
            details: {
                order: {},
                ups: {},
            },
            alias: {
                //PRESCRIPTION
                Repeats: 'Commercial Value',
                Name: 'Name',
                Surname: 'Surname',
                DAddress1: 'Delivery Address Line 1',
                DAddress2: 'Delivery Address Line 2',
                DAddress3: 'Delivery Town',
                DAddress4: 'Delivery Province',
                DPostcode: 'Delivery Postcode',
                DCountryCode: 'Delivery Country',
                Address1: 'Home Address Line 1',
                Address2: 'Home Address Line 2',
                Address3: 'Home Town',
                Address4: 'Home Province',
                Postcode: 'Home Postcode',
                CountryCode: 'Home Country',
                Telephone: 'Telephone',
                Email: 'Email',
                Notes: 'Notes',
                TokenID: 'COD Amount',
                TrackingCode: 'Tracking Code',
                DeliveryID: 'Delivery Company',
                //UPS
                APNotificationLanguage: 'UPS Notification Language',
                APNotificationValue: 'UPS Notification Email',
            },
            columnDelivery: ['Repeats', 'Name', 'Surname', 'DAddress1', 'DAddress2', 'DAddress3', 'DAddress4', 'DPostcode', 'DCountryCode', 'DeliveryID', 'TrackingCode'],
            columnHome: ['Address1', 'Address2', 'Address3', 'Address4', 'Postcode', 'CountryCode', 'Telephone', 'Email', 'TokenID', 'APNotificationLanguage', 'APNotificationValue'],
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
            this.confirmationChangesUPS = {};
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
                        this.postErrorToast(error);
                    })
            }
        },
        submit() {
            axios.post('/order-edit/' + this.orderID, { order: this.details.order, ups: this.details.ups })
                .then((response) => {
                    this.postSuccessToast('Saved');
                    this.close();
                    this.saveConfirmation = false;
                    this.emitter.emit('orderupdate');
                })
                .catch((error) => {
                    this.postErrorToast(error);
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
