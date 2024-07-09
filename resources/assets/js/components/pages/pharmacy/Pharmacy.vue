<template>
    <div class="content">
        <section class="card">
            <div class="card-header">
                <h3>Pharmacy details</h3>
            </div>
            <!-- Grid Row -->
            <div class="card-body pharmacy-edit">
                <form class="text-center p-5" v-on:submit.prevent="update">

                    <div
                        style="display: inline-flex;justify-content: space-around;flex-direction: column;width: 49%;padding: 10px;">
                        <p class="h4" style="margin-bottom: 10px;">Pharmacy Details</p>

                        <!-- Name -->
                        <label style="text-align: left;" for="pharmacyID">Pharmacy ID</label>
                        <input disabled :value="`Pharmacy ID: ${data.PharmacyID}`" type="text" id="PharmacyID"
                            class="form-control tBoxSize02 mb-10" placeholder="Pharmacy ID">

                        <!-- Name -->
                        <label style="text-align: left;" for="Title">Title</label>
                        <input v-model="data.Title" type="text" id="Title" class="form-control tBoxSize02 mb-10"
                            placeholder="Title">
                        <div v-if="errors.Title" class="invalid-feedback d-block">{{ errors.Title[0] }}</div>

                        <!-- Location -->
                        <label style="text-align: left;" for="Location">Location</label>
                        <input v-model="data.Location" type="text" id="Location" class="form-control tBoxSize02 mb-10"
                            placeholder="Location">
                        <div v-if="errors.Location" class="invalid-feedback d-block">{{ errors.Location[0] }}</div>

                        <!-- AccountNumber -->
                        <!-- <label style="text-align: left;" for="AccountNumber">DHL Shipper Account Number</label>
                        <input v-model="data.AccountNumber" type="text" id="AccountNumber" class="form-control tBoxSize02 mb-10" placeholder="AccountNumber">
                        <div v-if="errors.AccountNumber" class="invalid-feedback d-block">{{ errors.AccountNumber[0] }}</div> -->

                        <!-- AccountNumber -->
                        <!-- <label style="text-align: left;" for="BillingAccountNumber">DHL Billing Account Number</label>
                        <input v-model="data.BillingAccountNumber" type="text" id="BillingAccountNumber" class="form-control tBoxSize02 mb-10" placeholder="BillingAccountNumber">
                        <div v-if="errors.BillingAccountNumber" class="invalid-feedback d-block">{{ errors.BillingAccountNumber[0] }}</div> -->

                        <!-- ShipperName -->
                        <!-- <label style="text-align: left;" for="ShipperName">Shipper Name</label>
                        <input v-model="data.ShipperName" type="text" id="ShipperName" class="form-control tBoxSize02 mb-10"
                            placeholder="ShipperName">
                        <div v-if="errors.ShipperName" class="invalid-feedback d-block">{{ errors.ShipperName[0] }}</div> -->

                        <!-- VATNumber -->
                        <!-- <label style="text-align: left;" for="VATNumber">VAT Number</label>
                        <input v-model="data.VATNumber" type="text" id="VATNumber" class="form-control tBoxSize02 mb-10"
                            placeholder="VATNumber">
                        <div v-if="errors.VATNumber" class="invalid-feedback d-block">{{ errors.VATNumber[0] }}</div> -->

                        <!-- EORI -->
                        <!-- <label style="text-align: left;" for="EORI">EORI</label>
                        <input v-model="data.EORI" type="text" id="EORI" class="form-control tBoxSize02 mb-10"
                            placeholder="EORI">
                        <div v-if="errors.EORI" class="invalid-feedback d-block">{{ errors.EORI[0] }}</div> -->

                        <!-- Telephone -->
                        <label style="text-align: left;" for="Telephone">Shipper Telephone</label>
                        <input v-model="data.Telephone" type="text" id="Telephone" class="form-control tBoxSize02 mb-10"
                            placeholder="Telephone">
                        <div v-if="errors.Telephone" class="invalid-feedback d-block">{{ errors.Telephone[0] }}</div>

                        <!-- Email -->
                        <label style="text-align: left;" for="Email">Shipper Email</label>
                        <input v-model="data.Email" type="text" id="Email" class="form-control tBoxSize02 mb-10"
                            placeholder="Email">
                        <div v-if="errors.Email" class="invalid-feedback d-block">{{ errors.Email[0] }}</div>

                        <!-- EORI -->
                        <label style="text-align: left;" for="Contents">Contents</label>
                        <input v-model="data.Contents" type="text" id="Contents" class="form-control tBoxSize02 mb-10"
                            placeholder="Contents">
                        <small style="text-align: left;">This tag is used for DHL shipping</small>
                        <div v-if="errors.Contents" class="invalid-feedback d-block">{{ errors.Contents[0] }}</div>
                    </div>

                    <div
                        style="display: inline-flex;justify-content: space-around;flex-direction: column;width: 49%;padding: 10px;">
                        <p class="h4" style="margin-bottom: 10px;">Delivery Details</p>


                        <!-- Address1 -->
                        <label style="text-align: left;" for="Address1">Shipper Address 1</label>
                        <input v-model="data.Address1" type="text" id="Address1" class="form-control tBoxSize02 mb-10"
                            placeholder="Address1">
                        <div v-if="errors.Address1" class="invalid-feedback d-block">{{ errors.Address1[0] }}</div>

                        <!-- Address2 -->
                        <label style="text-align: left;" for="Address2">Shipper Address 2</label>
                        <input v-model="data.Address2" type="text" id="Address2" class="form-control tBoxSize02 mb-10"
                            placeholder="Address2">
                        <div v-if="errors.Address2" class="invalid-feedback d-block">{{ errors.Address2[0] }}</div>

                        <!-- Address3 -->
                        <label style="text-align: left;" for="Address3">Shipper Address 3</label>
                        <input v-model="data.Address3" type="text" id="Address3" class="form-control tBoxSize02 mb-10"
                            placeholder="Address3">
                        <div v-if="errors.Address3" class="invalid-feedback d-block">{{ errors.Address3[0] }}</div>

                        <!-- Address4 -->
                        <label style="text-align: left;" for="Address4">Shipper Address 4</label>
                        <input v-model="data.Address4" type="text" id="Address4" class="form-control tBoxSize02 mb-10"
                            placeholder="Address4">
                        <div v-if="errors.Address4" class="invalid-feedback d-block">{{ errors.Address4[0] }}</div>

                        <!-- Postcode -->
                        <label style="text-align: left;" for="Postcode">Shipper Postcode</label>
                        <input v-model="data.Postcode" type="text" id="Postcode" class="form-control tBoxSize02 mb-10"
                            placeholder="Postcode">
                        <div v-if="errors.Postcode" class="invalid-feedback d-block">{{ errors.Postcode[0] }}</div>

                        <!-- CountryCode -->
                        <label style="text-align: left;" for="CountryCode">Shipper Country Code</label>
                        <select v-model="data.CountryCode" class="browser-default custom-select mb-10" id="CountryCode">
                            <option disabled placeholder selected value="null">Select Country</option>
                            <option v-for="country in countries" :key="country.CountryID" :value="country.CountryID">{{
                                country.Name }}</option>
                        </select>

                        <div v-if="errors.CountryCode" class="invalid-feedback d-block">{{ errors.CountryCode[0] }}
                        </div>
                    </div>

                    <label style="text-align: center;" for="CountryBanlist">Country Blacklist</label>

                    <div class="center">
                        <treeselect style="width: 500px; max-width: 100%!important;" class="vue-treeselect-reports"
                            :open-on-click="true" :open-on-focus="true" :open-on-hover="true" :multiple="true"
                            :clearable="true" :searchable="true" :disable-branch-nodes="false"
                            placeholder="Select countries to blacklist" :show-count="true" :default-expand-level="1"
                            :append-to-body="false" :options="countries" @open="selectOpen" :normalizer="normalizer"
                            v-model="banlist">
                        </treeselect>
                    </div>

                    <hr style="margin-top: 20px;margin-bottom: 20px;">

                    <div class="card-header" style="margin-bottom: 20px;">
                        <h3>Client details</h3>
                    </div>

                    <div v-for=" client in data.clients " :key="client.ClientId"
                        style="display: inline-flex;justify-content: space-around;flex-direction: column;width: 33%;padding: 10px;">
                        <p class="h4" style="margin-bottom: 10px;"><b>{{ client.CompanyName }}</b> (ID: {{
                            client.ClientID
                            }})</p>

                        <!-- ShipperName -->
                        <label style="text-align: left;" :for="`ShipperName${client.ClientID}`">Shipper Name</label>
                        <input v-model="data.details[client.ClientID].ShipperName" type="text"
                            :id="`ShipperName${client.ClientID}`" class="form-control tBoxSize02 mb-10"
                            placeholder="ShipperName">
                        <div v-if="errors.ShipperName" class="invalid-feedback d-block">{{ errors.ShipperName[0] }}
                        </div>

                        <!-- AccountNumber -->
                        <label style="text-align: left;" :for="`AccountNumber${client.ClientID}`">DHL Shipper Account
                            Number</label>
                        <input v-model="data.details[client.ClientID].AccountNumber" type="text"
                            :id="`AccountNumber${client.ClientID}`" class="form-control tBoxSize02 mb-10"
                            placeholder="AccountNumber">
                        <div v-if="errors.AccountNumber" class="invalid-feedback d-block">{{ errors.AccountNumber[0] }}
                        </div>

                        <!-- BillingAccountNumber -->
                        <label style="text-align: left;" :for="`BillingAccountNumber${client.ClientID}`">DHL Billing
                            Account
                            Number</label>
                        <input v-model="data.details[client.ClientID].BillingAccountNumber" type="text"
                            :id="`BillingAccountNumber${client.ClientID}`" class="form-control tBoxSize02 mb-10"
                            placeholder="BillingAccountNumber">
                        <div v-if="errors.BillingAccountNumber" class="invalid-feedback d-block">{{
                            errors.BillingAccountNumber[0] }}</div>

                        <!-- VATNumber -->
                        <label style="text-align: left;" :for="`VATNumber${client.ClientID}`">VAT Number</label>
                        <input v-model="data.details[client.ClientID].VATNumber" type="text"
                            :id="`VATNumber${client.ClientID}`" class="form-control tBoxSize02 mb-10"
                            placeholder="VATNumber">
                        <div v-if="errors.VATNumber" class="invalid-feedback d-block">{{ errors.VATNumber[0] }}</div>

                        <!-- EORI -->
                        <label style="text-align: left;" :for="`EORI${client.ClientID}`">EORI</label>
                        <input v-model="data.details[client.ClientID].EORI" type="text" :id="`EORI${client.ClientID}`"
                            class="form-control tBoxSize02 mb-10" placeholder="EORI">
                        <div v-if="errors.EORI" class="invalid-feedback d-block">{{ errors.EORI[0] }}</div>
                    </div>

                    <hr style="margin-top: 20px;margin-bottom: 20px;">

                    <div>
                        <!-- Send button -->
                        <button class="btn btnSize01 secondaryBtn" type="submit">Update</button>
                    </div>

                    <hr style="margin-top: 20px;margin-bottom: 20px;">

                    <div class="prescriber__signature-upload clickable" style="display: inline-block;"
                        @click="inputClick">
                        <h4>Pharmacy Logo</h4>
                        <img style="width: 300px" class="mt-10" :src="imageSrc" alt="">
                        <input type="file" name="tracking" id="file" ref="file" accept=".jpg, .jpeg, .png"
                            @change="attachFile" />
                        <div class="input-mask mt-10">
                            <!-- <button type="button" class="browse-btn">
                                New Signature
                            </button> -->
                            <span class="file-info">{{ inputText }}</span>
                        </div>
                    </div>

                </form>
            </div>
            <!--Grid row-->
        </section>
    </div>
</template>
<style>
@import "@zanmato/vue3-treeselect/dist/vue3-treeselect.min.css";
</style>
<script>
import Error from '../../../mixins/errors'
import Treeselect from "@zanmato/vue3-treeselect";

export default {
    mixins: [Error],
    components: {
        Treeselect
    },
    data: function () {
        return {
            data: {},
            countries: [],
            banlist: [],
            loading: false,
            errors: {},
            userInfo: userInfo,
            file: '',
            fileDataURL: '',
        }
    },
    mounted() {
        if (userInfo.id != this.$route.params.id && userInfo.role < 50) {
            this.$router.push('/notallowed');
        } else {
            this.getData();
            this.getCountries();
            this.getBanList();
        }
    },
    computed: {
        inputText() {
            return this.file != '' ? this.file.name : 'Click to upload a new logo';
        },
        imageSrc() {
            if (this.file == '') {
                return `/pharmacies/logo/${this.$route.params.id}`;
            } else {
                return URL.createObjectURL(this.file);
            }
        },
        tableUrl: function () {
            return '/inventory/user/' + this.$route.params.id;
        },
        dataUrl: function () {
            return '/pharmacies/' + this.$route.params.id;
        },
        postUrl: function () {
            return '/pharmacies/' + this.$route.params.id;
        },
    },
    methods: {
        getData: function () {
            this.loading = true;

            axios.get(this.dataUrl)
                .then((response) => {
                    this.data = response.data.data;
                    this.loading = false;
                })
                .catch((error) => {
                    this.reportError(error);
                })
        },
        getCountries() {
            axios.get(`/countries`)
                .then((response) => {
                    this.countries = response.data.data;
                })
                .catch((error) => {
                    this.reportError(error);
                })
        },
        getBanList() {
            axios.get(`/pharmacies/${this.$route.params.id}/banlist`)
                .then((response) => {
                    this.banlist = [];
                    response.data.data.forEach(banned => {
                        this.banlist.push(banned.TypeID);
                    });
                })
                .catch((error) => {
                    this.reportError(error);
                })
        },
        update: function () {
            this.loading = true;

            if (this.file != '') {
                this.uploadSignature();
            }

            let postData = {
                Title: this.data.Title,
                Location: this.data.Location,
                AccountNumber: this.data.AccountNumber,
                BillingAccountNumber: this.data.BillingAccountNumber,
                ShipperName: this.data.ShipperName,
                VATNumber: this.data.VATNumber,
                EORI: this.data.EORI,
                Telephone: this.data.Telephone,
                Email: this.data.Email,
                Address1: this.data.Address1,
                Address2: this.data.Address2,
                Address3: this.data.Address3,
                Address4: this.data.Address4,
                Postcode: this.data.Postcode,
                Contents: this.data.Contents,
                CountryCode: this.data.CountryCode,
                BanList: this.banlist,
                Details: this.data.details,
            }

            axios.post(this.postUrl, postData)
                .then((response) => {
                    this.postSuccess(response.data.message);
                    this.errors = {};
                    this.loading = false;
                })
                .catch((error) => {
                    // this.postError(error.response.data.errors);
                    this.errors = error.response.data.errors;
                    this.loading = false;
                });
        },
        inputClick() {
            document.getElementById('file').click();
        },
        attachFile() {
            let files = document.getElementById('file').files;
            if (!files.length) {
                return;
            };

            this.file = files[0];
        },
        uploadSignature() {
            let formData = new FormData();
            formData.append('image', this.file);

            axios.post(`/pharmacies/logo/${this.$route.params.id}`, formData, { headers: { 'Content-type': 'multipart/form-data' } })
                .then((response) => {
                    this.postSuccess('Signature updated');
                    document.getElementById("file").value = '';
                    this.file = '';
                })
                .catch((error) => {
                    this.postSuccess('Failed to update signature');
                    this.file = '';
                });
        },
        selectOpen(instance) {
            //this is a specific fix for statuses since we need them to be wider than usual (when opening substatus selectors)
            if (instance == 'status-extended') {
                let element = document.querySelector('[data-instance-id="status-extended"]').firstChild
                element.classList.add('vue-treeselect__menu-container-body');
            }
        },
        normalizer(node) {
            return {
                id: node.CountryID,
                label: node.Name,
            }
        },
    }
}
</script>
