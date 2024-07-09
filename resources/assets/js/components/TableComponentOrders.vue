<script>
import TableComponent from './TableComponentSearch.vue';

export default {
    extends: TableComponent,
    methods: {
        deleteItem: function (id) {
            this.$swal({
                title: 'Are you sure you want to cancel this order?',
                type: 'warning',
                showConfirmButton: true,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes!'
            }).then((result) => {
                if (result.value) {
                    axios.delete(this.deleteUrl + '/' + id)
                        .then((response) => {
                            this.$emit('tableupdate');
                            this.postSuccess('Order successfully canceled!');
                            this.getData();
                        })
                        .catch((error) => {
                            console.log(error);
                            this.postError(error);
                        })

                }
            });
        }
    },
}
</script>
