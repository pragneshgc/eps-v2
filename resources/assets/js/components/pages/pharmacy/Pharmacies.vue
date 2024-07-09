<template>
    <div class="content">
        <section class="card">
            <div class="card-header">
                <h3>Pharmacies</h3>
            </div>
            <div class="card-body">
                <button class="btn btnSize01 secondaryBtn mb-10" @click="$router.push('/pharmacies/new')">
                    Add new pharmacy
                </button>

                <DataTable data-url="/pharmacies" column-class="col-lg-12" table-title="Pharmacies"
                    redirect-name="pharmacy" redirect-id="PharmacyID" :hidden-columns="['ID', 'Code']"
                    deleteUrl="/pharmacies" deleteId="PharmacyID">
                </DataTable>
            </div>
        </section>
    </div>
</template>

<script>
export default {
    data: function () {
        return {
            data: {},
            loading: false
        }
    },
}
</script>
