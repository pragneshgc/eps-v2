export default {
    methods: {
        download(data, type){
            let blob = new Blob([data], {type: 'application/'+type});
            let link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = this.currentOrderID+'.'+type;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },        
    },
}