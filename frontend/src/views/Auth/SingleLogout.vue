<template></template>
<script>
import { logout } from '../../services/service';

export default {
    data() { return {}; },
    async beforeCreate() {
        logout();
    },
};
</script>
