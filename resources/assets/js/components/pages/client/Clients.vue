<template>
    <div class="content">
        <section class="card">
            <div class="card-header">
                <h3>Clients</h3>
            </div>
            <div class="card-body">
                <button class="btn btnSize01 secondaryBtn mb-10" @click="$router.push('/clients/new')">
                    Add new client
                </button>

                <TableComponentSearch data-url="/clients/index" column-class="col-lg-12" table-title="Clients"
                    redirect-name="client" redirect-id="ID" :filters="filters" deleteUrl="/clients" deleteId="ID"
                    :csv-url-search="false" />
            </div>
        </section>
    </div>
</template>

<script>
import { defineAsyncComponent } from 'vue'
export default {
    components: {
        'TableComponentSearch': defineAsyncComponent(() => import('../../TableComponentSearch.vue')),
    },
    data: function () {
        return {
            filters: [
                {
                    title: 'Client Name',
                    value: 'name',
                    type: 'text',
                },
                {
                    title: 'Client Surname',
                    value: 'surname',
                    type: 'text',
                },
                {
                    title: 'Company Name',
                    value: 'companyname',
                    type: 'text',
                },
                {
                    title: 'Status',
                    value: 'status',
                    type: 'select',
                    selected: 1,
                    options: [
                        {
                            title: 'Active',
                            value: 1,
                        },
                        {
                            title: 'Inactive',
                            value: 0
                        },
                    ]
                },
            ]
        }
    },
}
</script>
