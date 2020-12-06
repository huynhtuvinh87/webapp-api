<template>
    <div>
        Contractor Compliance: {{ this.contractor_compliance }}% <br /> Employee Compliance: {{ this.employee_compliance }}%
    </div>
</template>

<script>
	export default {
		name: 'contractor-compliance',
        data(){
		    return {
		        employee_compliance : null,
                contractor_compliance : null
            }
        },
        props: {
		    contractor: String
        },
        async created(){

		    try {
                let response = await this.$http.get('/admin/contractor/'+ this.contractor + '/compliance')

                this.employee_compliance = response.data.employee
                this.contractor_compliance = response.data.contractor
            }
            catch(e){

		        console.log(e)

            }

        }
	};
</script>

<style scoped>

</style>
