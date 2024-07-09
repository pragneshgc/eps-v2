<template>
    <div>
        <div class="content">
            <!-- Prescription Stats-->
            <section>
                <div class="prescriptionStats flex-center">
                    <div class="title flex-align-center">Today's Orders</div>
                    <div class="list">
                        <ul v-if="loaded">
                            <!-- <li>
                                <span>Processing</span>{{ statistics.processing }}
                            </li> -->
                            <li>
                                <span>Pending</span>{{ statistics.ready }}
                            </li>
                            <li>
                                <span>Shipped</span>{{ statistics.shipped }}
                            </li>
                            <li>
                                <span>Total</span>{{ statistics.total }}
                            </li>
                        </ul>
                        <ul style="overflow: hidden;" v-else>
                            <li>
                                <div class="loader loader-relative" style="">Loading...</div>
                            </li>
                        </ul>
                    </div>
                    <!-- <div v-if="loaded" class="total flex-align-center"><span>Total</span>{{ statistics.total }}</div> -->
                </div>
            </section>
            <!-- End Prescription Stats-->
            <section>
                <div class="orderSearch flexContent">
                    <h3>Search order</h3>
                    <form @submit.prevent="search" autocomplete="on">
                        <div class="formItemsGroup floatLeft flex mt-20">
                            <input required v-model="orderID" id="orderID" class="tBox tBoxSize02" type="text"
                                placeholder="Order No" />
                            <button class="btn btnSize02 tertiaryBtn" type="submit">
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </section>
            <section v-if="orderDetails">
                <div class="orderDetails">
                    {{ orderDetails }}
                </div>
            </section>
        </div>
    </div>
</template>

<script>
import Error from '../../mixins/errors'

export default {
    data: function () {
        return {
            pharmacies: [],
            statistics: { processing: 0, ready: 0, import: 0, dpd: 0, ups: 0, dhl: 0, rml: 0, shipped: 0, total: 0 },
            loaded: false,
            orderID: '',
            orderDetails: false
        }
    },
    mounted() {
        this.getStatistics();
        this.getPharmacies();
        document.getElementById("orderID").focus();
    },
    methods: {
        search() {
            this.$router.push({ name: 'order', params: { id: this.orderID } });
        },
        getPharmacies() {
            axios.get(`/pharmacies/list`)
                .then((response) => {
                    let r = response.data.data;
                    r.forEach(result => {
                        this.pharmacies.push({
                            id: result.PharmacyID,
                            value: result.PharmacyID,
                            label: result.Title
                        });
                    });

                    console.log('pharmacy', this.pharmacies);
                })
                .catch((error) => {
                    console.log(error);
                })
        },
        getStatistics() {
            axios.get('/statistics')
                .then((response) => {
                    this.statistics.processing = response.data.data.statistics.processing;
                    this.statistics.ready = response.data.data.statistics.ready;
                    this.statistics.import = response.data.data.statistics.import;
                    this.statistics.shipped = response.data.data.shipped;
                    this.statistics.dpd = response.data.data.statistics.dpd;
                    this.statistics.ups = response.data.data.statistics.ups;
                    this.statistics.dhl = response.data.data.statistics.dhl;
                    this.statistics.rml = response.data.data.statistics.rml;
                    this.statistics.total = response.data.data.total;
                    this.loaded = true;
                })
                .catch((error) => {
                    this.postError(error.response.data.message);
                })
        }
    },
}
</script>
