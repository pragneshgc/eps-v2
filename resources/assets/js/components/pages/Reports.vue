<template>
    <div class="content">
        <section class="card">
            <div class="card-header">
                <h3>Reports</h3>
            </div>
            <div class="card-body">
                <div class="orderSearch">
                    <TableComponentSearch data-url="/orders/search" column-class="col-lg-12" table-title="Activity Reports"
                        redirect-name="order" redirect-id="PrescriptionID"
                        :hidden-columns="['checked', 'ActivityID', 'Arguments']" :filters="filters"
                        :column-map="columnMap" />
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import orderStatuses from '../../mixins/constants/orderStatuses'

export default {
    mixins: [orderStatuses],
    data: function () {
        return {
            columnMap: {
                'PrescriptionID': 'ID',
                'DeliveryID': 'Delivery Company',
                'CompanyName': 'Client',
                'ReferenceNumber': 'Ref',
                'Date2': 'Date',
                'Min': 'Time'
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
                // {
                //     title: 'Timestamp',
                //     value: 'timestamp',
                //     type: 'select',
                //     options: [
                //         {
                //             title: 'Select Date Type',
                //             value: ''
                //         },
                //         {
                //             title: 'Recieved Date',
                //             value: 'recieved_date'
                //         },
                //         {
                //             title: 'Processed Date',
                //             value: 'processed_date'
                //         }
                //     ]
                // },
                {
                    title: 'Reference Number',
                    value: 'reference',
                    type: 'text',
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
        'TableComponentSearch': () => import('../TableComponentSearch.vue'),
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
                default:
                    break;
            }
        });
    },
    methods: {

    }
}
</script>
