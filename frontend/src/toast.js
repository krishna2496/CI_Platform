export default {
    makeToast(variant = null) {
        this.$bvToast.toast('"Application deadline is passed. You cannot apply for this mission', {
            variant: variant,
            solid: true,
            autoHideDelay: 800000
        })
    }
}