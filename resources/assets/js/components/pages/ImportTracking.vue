<template>
    <div>
        <div class="content">
            <!-- Prescription Stats-->
            <section class="card card-border pb-20 text-center">
                <h3 class="mb-20">Import tracking codes</h3>

                <select v-model="option" class="browser-default custom-select big-select">
                    <option value="" disabled>Please select an option</option>
                    <option v-for="company in optionList" :value="company.SettingID">{{ company.Name }}</option>
                </select>

                <div v-show="option != ''" class="input-container mt-20">
                    <input type="file" name="tracking" id="file" ref="file" @change="attachFile"/>
                    <div class="input-mask" @click="inputClick">
                        <button class="browse-btn">
                            {{buttonText}}
                        </button>
                        <span class="file-info">{{inputText}}</span>
                    </div>
                </div>
            </section>
            <!-- End Prescription Stats-->

            <section class="text-center">
                <button :disabled="importing" v-if="file != '' && option != ''" class="btn btnSize01 tertiaryBtn bigButton mr-10 big-round-button" @click="upload">
                    <div v-if="importing" class="loader" style="">Loading...</div>
                    <span v-else>Import</span>
                </button>
            </section>

            <section v-show="trackingStatuses.length != 0" class="card mt-10">
                <ul class="tracking-list">
                    <li v-for="(key, value) in trackingStatuses">
                        <a target="_blank" :href="'#/order/'+value">{{value}}</a> - {{key}}
                    </li>
                </ul>
            </section>
        </div>        
    </div>
</template>

<script>
    import Error from '../../mixins/errors'

    export default {
        mixins: [ Error ],
        data: function () {
            return {
                file: '',
                option: '',
                importing: false,
                optionList: [],
                trackingStatuses: [],
            }
        },
        watch: {
            option(){
                if(this.option != ''){
                    this.trackingStatuses = [];
                }
            }
        },
        computed: {
            inputText(){
                return this.file != '' ? this.file.name : 'Upload a file';
            },
            buttonText(){
                return this.file != '' ? 'Importing tracking' : 'Upload';
            }
        },
        mounted() {
            axios.get('/delivery-companies')
            .then((response) => {
                this.optionList = response.data.data;
            });
        },
        methods: {
            inputClick(){
                document.getElementById('file').click();
            },
            attachFile(){
                let files = document.getElementById('file').files;
                if (!files.length) {
                    return;
                };

                this.file = files[0];
            },
            upload(){
                this.importing = true;
                let formData = new FormData();
                formData.append('file', this.file);
                formData.append('option', this.option);

                axios.post('/import-tracking', formData, {headers: {'Content-type': 'multipart/form-data'}})
                .then((response) => {
                    this.trackingStatuses = response.data.data;
                    this.postSuccess('Import tracking uploaded');
                    document.getElementById("file").value = '';
                    this.file = '';
                    this.option = '';
                    this.importing = false;
                })
                .catch((error) => {
                    this.postSuccess('Import tracking could not be uploaded');
                    this.file = '';
                    this.option = '';
                    this.importing = false;
                });
            }
        },
    }
</script>
