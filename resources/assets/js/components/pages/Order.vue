<template>
    <div>
        <div class="content">
            <section>
                <div class="orderSearch flexContent">
                    <h3>Search order</h3>
                    <form @submit.prevent="search" autocomplete="on">
                        <div class="formItemsGroup floatLeft flex mt-20">
                            <input required v-model="orderID" class="tBox tBoxSize02" type="text" id="orderID"
                                placeholder="Order No" />
                            <button class="btn btnSize02 tertiaryBtn" type="submit">
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <transition name="fade">
                <section class="flexContent" v-if="!loading && orderDetails != null">
                    <h2>Order Details</h2>
                    <div class="orderDetails">
                        <img src="../../../images/iconPaper.png" />
                        <ul>
                            <li><span>Order #: </span>{{ orderDetails.ReferenceNumber }}</li>
                            <li><span>Name: </span>{{ orderDetails.Name }}</li>
                            <li><span>Surname: </span>{{ orderDetails.Surname }}</li>
                            <li><span>Status: </span>{{ orderStatuses[orderDetails.Status] }}</li>
                            <li v-if="orderDetails.TrackingCode != '' && orderDetails.TrackingCode != null">
                                <span>Tracking
                                    Code: </span>{{ orderDetails.TrackingCode }}</li>
                            <!-- <li v-if="orderDetails.Repeats != '0' && orderDetails.Repeats != '' && [143, 162, 205, 243].includes(orderDetails.DCountryCode)">
                                <span>Commercial value: </span>{{ orderDetails.Repeats }}
                            </li> -->
                            <li>
                                <button @click="editDetails(orderDetails)" class="btn btnSize02 tertiaryBtn"
                                    type="button">
                                    Edit Address
                                </button>
                                <transition name="fade">
                                    <a :href="`/order/${orderDetails.PrescriptionID}/download-document`"
                                        target="_blank">
                                        <button v-if="hasDocumentation" title="Download Documentation"
                                            class="btn btnSize02 tertiaryBtn" type="button">
                                            <i class="fa fa-file"></i>
                                        </button>
                                    </a>
                                </transition>
                            </li>
                        </ul>
                    </div>
                </section>
            </transition>

            <transition name="fade">
                <section class="flexContent" v-if="!loading && orderDetails == null">
                    Order Not Found
                </section>
            </transition>

            <transition name="fade">
                <section class="flexContent flex-direction-row" v-if="!loading && orderDetails != null">
                    <img style="height: 50px;" :src="imgMap[orderDetails.DeliveryID]" />
                </section>
            </transition>

            <transition name="fade">
                <section class="flexContent flex-direction-row"
                    v-if="!loading && orderDetails != null && orderDetails.Decomissioned && [7, 8].includes(orderDetails.Status)">
                    <div style="width: 100%;">
                        <div style="display:flex; justify-content:center;"
                            v-if="orderDetails.DeliveryID == '7' || orderDetails.DeliveryID == '10' || orderDetails.DeliveryID == '4'">
                            <button @click="request()" :disabled="requestedPrint || orderDetails.DeliveryID == '7'"
                                v-if="(orderDetails.TrackingCode == '' || orderDetails.TrackingCode == null) && !isManual/* && (!isManual && !isCommercialPaper)*/"
                                class="btn btnSize01 tertiaryBtn bigButton mr-10 big-round-button" type="submit">
                                <div v-if="requestedPrint" class="loader" style="">Loading...</div>
                                <span v-else>Request Label</span>
                            </button>
                            <button @click="print(true)" :disabled="requestedPrint"
                                v-else-if="!isManual/* && !isCommercialPaper*/"
                                class="btn btnSize01 tertiaryBtn bigButton big-round-button" type="submit">
                                Reprint Label
                            </button>
                            <!-- <button @click="manual()" v-if="orderDetails.DeliveryID == '7' && (isManual || isCommercialPaper)"
                            :disabled="true"
                            class="btn btnSize01 tertiaryBtn bigButton big-round-button" type="submit">
                                {{manualPrintText}}
                            </button>
                            <button @click="manual()" v-else-if="orderDetails.DeliveryID == '10' && (isManual || isCommercialPaper)" class="btn btnSize01 tertiaryBtn bigButton big-round-button" type="submit">
                                {{manualPrintText}}
                            </button>
                            <button @click="manual()" v-else-if="orderDetails.DeliveryID == '10'" class="btn btnSize01 tertiaryBtn bigButton big-round-button" type="submit">
                                {{manualPrintText}}
                            </button>

                            <button @click="manual()" v-else-if="orderDetails.DeliveryID == '7' && isCommercial && !isCommercialPaper"
                            :disabled="true"
                            class="btn btnSize01 tertiaryBtn" type="submit">
                                {{manualPrintText}}
                            </button>    -->

                            <button @click="reset()" :disabled="requestedPrint"
                                v-if="!isManual && (orderDetails.TrackingCode != '' && orderDetails.TrackingCode != null)"
                                class="btn btnSize01 tertiaryBtn bigButton" type="submit">
                                Reset Label
                            </button>
                        </div>
                        <!-- <div v-else>
                            <button @click="manual()" class="btn btnSize01 tertiaryBtn bigButton big-round-button" type="submit">
                                {{manualPrintText}}
                            </button>
                        </div> -->
                    </div>
                </section>
            </transition>

            <transition name="fade">
                <section v-if="!loading && orderDetails == null">
                    <div class="infoBox warning">
                        <p>Order with number {{ this.currentOrderID }} was not found!</p>
                    </div>
                </section>
            </transition>

            <transition name="fade">
                <section
                    v-if="!loading && orderDetails != null && (orderDetails.Repeats == '0' || orderDetails.Repeats == '') && [143, 162, 205, 243].includes(orderDetails.DCountryCode)">
                    <div class="infoBox warning">
                        <p>
                            THIS ORDER HAS NO COMMERCIAL VALUE. PLEASE SET VALUE BY CLICKING EDIT.
                        </p>
                    </div>
                </section>
            </transition>

            <transition name="fade">
                <section v-if="!loading && orderDetails != null && !orderDetails.Decomissioned">
                    <div class="infoBox warning">
                        <p>
                            THIS ORDER IS NOT READY TO BE SHIPPED. IT NEEDS TO BE DECOMMISSIONED FIRST
                        </p>
                    </div>
                </section>
                <section v-else-if="!loading && ![7, 8].includes(orderDetails.Status)">
                    <div class="infoBox warning">
                        <p>
                            THIS ORDER IS NOT READY TO BE SHIPPED. THE ORDER STATUS IS <b>{{
                                orderStatuses[orderDetails.Status] }}</b>
                        </p>
                    </div>
                </section>
            </transition>

            <transition name="fade">
                <section class="activity" v-if="activity.length > 0 && !loadingActivity && !loading">
                    <h5 class="activity-log-header">Activities on this order</h5>

                    <transition-group name="slide-down" tag="ul" class="activity-log-wrapper"
                        style="margin-top: 0px!important;">
                        <li class="activity-log-item" v-for="a in activity" :key="a.ActivityID">
                            <div class="activity-action">
                                {{ a.Action }}
                                <button style="background: red; border: 1px solid red;"
                                    v-if="a.Action == 'DHL request label - PDF issue' && a.Status == 1"
                                    class="btn btnSize02 tertiaryBtn" @click="resendPDF()">
                                    Resend PDF
                                </button>
                            </div>
                            <div class="activity-footer">
                                <span>{{ a.Name }}</span><span>{{ a.Date }}</span>
                            </div>
                        </li>
                    </transition-group>
                </section>

                <section class="activity" v-else-if="!loadingActivity && !loading">
                    <ul class="activity-log-wrapper">
                        <li class="activity-log-item">
                            No activity log found
                        </li>
                    </ul>
                </section>
            </transition>

            <transition name="fade">
                <EditOrderAddress @closeorder="editingOrder = !editingOrder"
                    :products="isCommercial ? orderDetails.Products : []" :orderID="orderDetails.PrescriptionID"
                    :orderStatus="orderStatuses[orderDetails.Status]" v-if="editingOrder" />
            </transition>
        </div>
    </div>
</template>

<script>
import Error from '../../mixins/errors'
import Print from '../../mixins/print';
import Download from '../../mixins/download';
//constants
import OrderStatuses from '../../mixins/constants/orderStatuses';
import EditOrderAddress from './EditOrderAddress.vue';

export default {
    mixins: [Error, Print, OrderStatuses, Download],
    components: {
        EditOrderAddress
    },
    data: function () {
        return {
            orderID: this.$route.params.id,
            userInfo: userInfo,
            editingOrder: false,
            currentOrderID: '',
            orderDetails: false,
            loading: false,
            loadingActivity: false,
            requestedPrint: false,
            hasDocumentation: false,
            label: '',
            imgMap: {
                3: 'images/logo/tnt.png',
                4: 'images/logo/dpd.png',
                5: 'images/logo/rmail.png',
                7: 'images/logo/ups.png',
                8: 'images/logo/tnt.png',
                10: 'images/logo/dhl.png',
            },
            activity: [],
        }
    },
    created() {
        this.search();

        this.emitter.on('orderupdate', (e) => {
            this.search();
        });
    },
    mounted() {
        //document.addEventListener('focusin', function(e) { console.log(e)})
        // this.getActivity();
        document.getElementById("orderID").focus();
    },
    destroyed() {
        this.emitter.off('orderupdate');
    },
    computed: {
        isCommercial() {
            return this.orderDetails.Repeats != '0'
                && this.orderDetails.Repeats != ''
                && [143, 162, 205, 243].includes(this.orderDetails.DCountryCode);
        },
        isCommercialPaper() {
            return this.orderDetails.Repeats != '0'
                && this.orderDetails.Repeats != ''
                && [143].includes(this.orderDetails.DCountryCode);
        },
        isCOD() {
            return this.orderDetails.PaymentMethod != '0';
        },
        isManual() {
            // return this.orderDetails.PaymentMethod != '0';// ||
            //this.orderDetails.UPSAccessPointAddress != '0' ||
            // [143, 162, 205, 243].includes(this.orderDetails.DCountryCode)

            return false;
        },
        manualPrintText() {
            return this.orderDetails.Printed ? 'Manual Re-print' : 'Manual Print';
        },
        apiEndpoint() {
            if (this.orderDetails != null) {
                if (this.orderDetails.DeliveryID == '10') {
                    return '/api/dhl/shipment-validation/' + this.orderDetails.PrescriptionID;
                } else if (this.orderDetails.DeliveryID == '7') {
                    return '/api/ups/shipment-validation/' + this.orderDetails.PrescriptionID;
                } else if (this.orderDetails.DeliveryID == '4') {
                    return '/api/dpd/shipment-validation/' + this.orderDetails.PrescriptionID;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        },
        labelEndpoint() {
            if (this.orderDetails != null) {
                if (this.orderDetails.DeliveryID == '10') {
                    return '/api/dhl/label/' + this.orderDetails.PrescriptionID;
                } else if (this.orderDetails.DeliveryID == '7') {
                    if (this.isCOD) {
                        return '/api/ups/gif/' + this.orderDetails.PrescriptionID;
                    } else {
                        return '/api/ups/label/' + this.orderDetails.PrescriptionID;
                    }
                } else if (this.orderDetails.DeliveryID == '4') {
                    return '/api/dpd/label/' + this.orderDetails.PrescriptionID;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        },
        manualEndpoint() {
            if (this.orderDetails != null) {
                if (this.orderDetails.DeliveryID == '10') {
                    return '/api/dhl/manual/' + this.orderDetails.PrescriptionID;
                } else if (this.orderDetails.DeliveryID == '7') {
                    return '/api/ups/manual/' + this.orderDetails.PrescriptionID;
                } else if (this.orderDetails.DeliveryID == '3') {
                    return '/api/tnt/manual/' + this.orderDetails.PrescriptionID;
                } else if (this.orderDetails.DeliveryID == '4') {
                    return '/api/dpd/manual/' + this.orderDetails.PrescriptionID;
                } else if (this.orderDetails.DeliveryID == '5') {
                    return '/api/rmail/manual/' + this.orderDetails.PrescriptionID;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        },
        autoprint() {
            return this.orderDetails.DeliveryID == '10' ? false : true;
        }
    },
    watch: {
        '$route.params'() {
            if (typeof this.$route.params.id != 'undefined' && this.currentOrderID != this.$route.params.id) {
                this.orderID = this.$route.params.id;
                this.search();
            }
        },
    },
    methods: {
        getActivity() {
            this.loadingActivity = true;

            axios.get('/order/' + this.orderDetails.PrescriptionID + '/activity')
                .then((response) => {
                    this.activity = response.data.data;
                    this.loadingActivity = false;
                })
                .catch((error) => {
                    this.postError(error.response.data.message);
                    this.loadingActivity = false;
                })
        },
        //check and download if the order has any ready docs
        checkDocument() {
            axios.get(`/order/${this.orderDetails.PrescriptionID}/check-document`)
                .then((response) => {
                    this.hasDocumentation = response.data.data;
                })
                .catch((error) => {
                    this.postErrorToast(error.response.data.message);
                })
        },
        downloadDocument() {
            axios.get(`/order/${this.orderDetails.PrescriptionID}/download-document`)
                .then((response) => {
                    this.download(response.data, 'pdf');
                })
                .catch((error) => {
                    this.postErrorToast(error.response.data.message);
                })
        },
        search() {
            if (this.orderID != '') {
                this.currentOrderID = this.orderID;
                this.orderID = '';
            }

            this.loading = true;
            axios.get('/order/' + this.currentOrderID)
                .then((response) => {
                    this.orderDetails = response.data.data;
                    this.checkDocument();
                    this.loading = false;
                    this.getActivity();
                })
                .catch((error) => {
                    this.orderDetails = null;
                    this.postErrorToast(error.response.data.message);
                    this.loading = false;
                })

            if (this.$route.params.id != this.currentOrderID) {
                this.$router.push({ params: { id: this.currentOrderID } });
            }

        },
        focusInput() {
            setTimeout(() => {
                document.getElementById("orderID").focus();
            }, 300);
        },
        editDetails(orderDetails) {
            this.editingOrder = !this.editingOrder;
        },
        commercialInvoiceNotification(callback) {
            callback();

            // if(this.isCommercial){
            //     let products = '';
            //     for(let i = 0; i<this.orderDetails.Products.length; i++){
            //         products += this.orderDetails.Products[i].Description;
            //         if(i+1 != this.orderDetails.Products.length){
            //             products += ',';
            //         }
            //     }
            //     this.$swal({
            //         title: 'This order needs a commercial invoice',
            //         html: "<b>Value:</b> "+this.orderDetails.Repeats+"<br> <b>Products:</b> "+products,
            //         type: 'warning',
            //         showCancelButton: true,
            //         confirmButtonColor: '#3085d6',
            //         cancelButtonColor: '#d33',
            //         confirmButtonText: 'Ok'
            //     }).then((result) => {
            //         if (result.value) {
            //             callback();
            //         } else {
            //             this.focusInput();
            //         }
            //     })
            // } else {
            //     callback();
            // }
        },
        request() {
            this.commercialInvoiceNotification(() => {
                this.requestedPrint = true;

                if (this.apiEndpoint) {
                    axios.get(this.apiEndpoint)
                        .then((response) => {
                            //fetch backend parameters here
                            if (response.data.data.paper_invoice) {
                                this.printUrl(`${window.location.origin}/token/ups/invoice/${this.orderDetails.PrescriptionID}?token=${userInfo.token}`);
                            }

                            if (response.data.data.gif) {
                                this.printFile(`${window.location.origin}/api/ups/gif/${this.orderDetails.PrescriptionID}?token=${userInfo.token}`, () => {
                                    // this.printHtml(`${window.location.origin}/token/ups/cod/${this.currentOrderID}?token=${userInfo.token}`);
                                });
                            } else {
                                this.printPage(this.labelEndpoint, this.autoprint);
                            }

                            if (response.data.data.resend) {
                                this.reportErrorToast('Prescription PDF not sent to DHL! Trying to resend.');
                                this.resendPDF();
                            }

                            this.orderID = this.currentOrderID;
                            this.search();
                            this.requestedPrint = false;
                            this.focusInput();
                            this.getActivity();
                        })
                        .catch((error) => {
                            // this.printPage(this.labelEndpoint, this.autoprint); // do NOT print anything if there was an error
                            this.postErrorPopup(error.response.data.message);
                            this.search();
                            this.requestedPrint = false;
                            this.focusInput();
                            this.getActivity();
                        })
                }
            });
        },
        resendPDF() {
            axios.post(`/api/dhl/${this.orderDetails.PrescriptionID}/resend-pdf`)
                .then((response) => {
                    this.search();
                    this.getActivity();
                })
                .catch((error) => {
                    this.postError(error.response.data.message);
                })
        },
        manual() {
            if (this.manualEndpoint) {
                this.requestedPrint = true;
                axios.get(this.manualEndpoint)
                    .then((response) => {
                        if (this.orderDetails.Printed) {
                            // this.authorize(() => {
                            let type = this.orderDetails.DeliveryID == '7' ? 'xml' : 'csv';
                            this.download(response.data, type);
                            this.orderDetails.Printed = true;
                            this.requestedPrint = false;
                            this.focusInput();
                            // });
                        } else {
                            let type = this.orderDetails.DeliveryID == '7' ? 'xml' : 'csv';
                            this.download(response.data, type);
                            this.orderDetails.Printed = true;
                            this.requestedPrint = false;
                        }

                        this.getActivity();
                    })
                    .catch((error) => {
                        this.postError(error.response.data.message);
                        this.requestedPrint = false;
                        this.focusInput();
                        this.getActivity();
                    })
            }
        },
        print(reprint = false) {
            // this.authorize(() => {
            this.requestedPrint = true;

            if (this.isCOD) {
                this.printFile(`${window.location.origin}/api/ups/gif/${this.orderDetails.PrescriptionID}?token=${userInfo.token}`, () => {
                    // this.printHtml(`${window.location.origin}/token/ups/cod/${this.currentOrderID}?token=${userInfo.token}`);
                });
            } else {
                this.printPage(this.labelEndpoint, this.autoprint);
            }

            if (this.isCommercialPaper) {
                this.printUrl(`${window.location.origin}/token/ups/invoice/${this.orderDetails.PrescriptionID}?token=${userInfo.token}`);
            }

            this.search(this.orderDetails.ReferenceNumber);
            this.requestedPrint = false;
            this.focusInput();
            // });
        },
        reset() {
            axios.get('/reset-order/' + this.orderDetails.PrescriptionID)
                .then((response) => {
                    this.search(this.orderDetails.ReferenceNumber);
                    this.getActivity();
                    console.warn(response);
                })
                .catch((error) => {
                    console.warn(error);
                    this.getActivity();
                })
        },
        // authorize(callback){
        //     this.$swal({
        //         text: 'Scan authorization code',
        //         input: 'text',
        //         inputValue: '',
        //         inputClass: 'key',
        //         inputAttributes: {
        //             'autocomplete': 'off',
        //             'type': 'hidden',
        //         },
        //         showCancelButton: true,
        //         inputValidator: (value) => {
        //             return new Promise((resolve) => {
        //                 if(resolve != ''){
        //                     axios.post('/resend-authorization', {code: value})
        //                     .then((respond) => {
        //                         resolve();
        //                     })
        //                     .catch((error) => {
        //                         resolve('The authorization code is incorrect');
        //                     })
        //                 } else {
        //                     resolve('The authorization code can not be empty');
        //                 }

        //             })
        //         }
        //     }).then((result) => {
        //         if (typeof result.value != 'undefined' && result.value != '') {
        //             let reasons = {
        //                 '1': 'System unresponsive',
        //                 '2': 'Damaged label',
        //                 '3': 'Label applied incorrectly',
        //             }

        //             let code = result.value;

        //             this.$swal({
        //                 text: 'Why is this label being reprinted?',
        //                 input: 'select',
        //                 inputOptions: reasons,
        //                 inputPlaceholder: 'Select a reason',
        //                 confirmButtonText: 'Resend',
        //                 showCancelButton: true,
        //                 inputValidator: (value) => {
        //                     return new Promise((resolve) => {
        //                         if (value === '1' || value === '2' || value === '3') {
        //                             resolve();
        //                         } else {
        //                             resolve('Reason invalid');
        //                         }
        //                     })
        //                 }
        //             }).then((result) => {
        //                 if (result.value) {
        //                     callback();
        //                     axios.post('/log-reprint', {orderID: this.orderDetails.PrescriptionID, reason: reasons[result.value], code: code})
        //                     .then((response) => {
        //                         console.log(response);
        //                     })
        //                     .catch((error) => {
        //                         console.log(error);
        //                     })
        //                 } else {
        //                     this.focusInput();
        //                 }
        //             })
        //         } else {
        //             this.focusInput();
        //         }
        //     })
        // },
    }
}
</script>
