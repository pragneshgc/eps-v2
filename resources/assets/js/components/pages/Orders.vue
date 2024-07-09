<template>
    <div class="content">
        <section class="card">
            <div class="card-header">
                <h3>Orders</h3>
            </div>
            <div class="card-body">
                <div class="orderSearch">
                    <TableComponentSearch data-url="/orders" column-class="col-lg-12" table-title="Orders"
                        redirect-name="order" redirect-id="ReferenceNumber"
                        :hidden-columns="['checked', 'ActivityID', 'Arguments']" :filters="filters"
                        :column-map="columnMap" deleteUrl="/orders" deleteId="ReferenceNumber"
                        :not-orderable="['Pharmacies']" />
                </div>
            </div>
        </section>
    </div>
</template>

<style>
@import "@zanmato/vue3-treeselect/dist/vue3-treeselect.min.css";
</style>


<script>
import { defineAsyncComponent } from 'vue'
import orderStatuses from '../../mixins/constants/orderStatuses'
import filtersData from '../../mixins/filtersData'
import Treeselect from "@zanmato/vue3-treeselect";

export default {
    mixins: [orderStatuses, filtersData],
    components: {
        Treeselect
    },
    data: function () {
        return {
            pharmacies: [],
            userInfo: userInfo,
            columnMap: {
                'PrescriptionID': 'ID',
                'DeliveryID': 'Delivery Company',
                'CompanyName': 'Client',
                'ReferenceNumber': 'Reference Number',
            },
            filters: [
                {
                    title: 'Start Date',
                    value: 'start_date',
                    type: 'date',
                },
                {
                    title: 'End Date',
                    value: 'end_date',
                    type: 'date',
                },
                {
                    title: 'Timestamp',
                    value: 'timestamp',
                    type: 'select',
                    options: [
                        {
                            title: 'Select Date Type',
                            value: ''
                        },
                        {
                            title: 'Recieved Date',
                            value: 'recieved_date'
                        },
                        {
                            title: 'Processed Date',
                            value: 'processed_date'
                        }
                    ]
                },
                {
                    title: 'Reference Number',
                    value: 'reference',
                    type: 'text',
                },
                {
                    title: 'Pharmacy',
                    value: 'pharmacy',
                    type: 'select-normal',
                    options: this.pharmacies,
                    loadOptions: this.pharmacies,
                    multiple: true,
                    clearable: true,
                    placeholder: 'Select Pharmacy',
                },
                {
                    title: 'Status',
                    value: 'status',
                    type: 'select',
                    clearable: true,
                    placeholder: 'Select Prescription Status',
                    options: []
                },
                {
                    title: 'Order ID',
                    value: 'order_id',
                    type: 'text',
                },
            ]
        }
    },
    components: {
        'TableComponentSearch': defineAsyncComponent(() => import('../TableComponentSearch.vue')),
    },
    mounted() {
        this.filters.find((o, i) => {
            switch (o.value) {
                case 'status':
                    o.options = this.orderStatusesSelect;
                    break;
                case 'doctor':
                    axios.get('/doctors')
                        .then((response) => {
                            response.data.data.forEach(doctor => {
                                o.options.push({
                                    title: doctor.Title + ' ' + doctor.Name + ' ' + doctor.Surname,
                                    value: doctor.DoctorID
                                });
                            });
                        })
                        .catch((error) => {
                            console.log(error);
                        })
                    // o.options = this.orderStatusesSelect;
                    break;
                case 'country':
                    axios.get('/countries')
                        .then((response) => {
                            response.data.data.forEach(country => {
                                o.options.push({
                                    title: country.Name,
                                    value: country.CountryID
                                });
                            });
                        })
                        .catch((error) => {
                            console.log(error);
                        })
                    // o.options = this.orderStatusesSelect;
                    break;
                case 'product':
                    axios.get('/products')
                        .then((response) => {
                            response.data.data.forEach(product => {
                                o.options.push({
                                    title: product.Name,
                                    value: product.Code
                                });
                            });
                        })
                        .catch((error) => {
                            console.log(error);
                        })
                    // o.options = this.orderStatusesSelect;
                    break;
                case 'delivery':
                    axios.get('/delivery-companies')
                        .then((response) => {
                            response.data.data.forEach(company => {
                                o.options.push({
                                    title: company.Name,
                                    value: company.SettingID
                                });
                            });
                        })
                        .catch((error) => {
                            console.log(error);
                        })
                    // o.options = this.orderStatusesSelect;
                    break;
                case 'client':
                    axios.get('/clients')
                        .then((response) => {
                            response.data.data.forEach(client => {
                                o.options.push({
                                    title: client.CompanyName,
                                    value: client.ClientID
                                });
                            });
                        })
                        .catch((error) => {
                            console.log(error);
                        })
                    // o.options = this.orderStatusesSelect;
                    break;
                case 'pharmacy':
                    o.options = this.pharmacies;
                    break;
                default:
                    break;
            }
        });

        this.getPharmacies();
    },
    methods: {
        getPharmacies() {
            axios.get(`/pharmacies/list`)
                .then((response) => {
                    let r = response.data.data;

                    r.forEach(result => {
                        this.pharmacies.push({
                            id: result.PharmacyID,
                            //value: result.Title,
                            label: result.Title
                        });
                    });
                })
                .catch((error) => {
                    console.log(error);
                })
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
                id: node.PharmacyID,
                label: node.Title,
            }
        },
    }
}
</script>
