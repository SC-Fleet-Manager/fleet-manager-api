<template>
    <select :multiple="multiple">
        <slot></slot>
    </select>
</template>

<script>
    import 'select2';
    import 'select2/dist/css/select2.min.css';

    export default {
        name: 'select2',
        props: ['options', 'value', 'multiple'],
        mounted() {
            $(this.$el)
                // init select2
                .select2({ data: this.options })
                .val(this.value)
                .trigger('change')
                // emit event on change.
                .on('change', (ev) => {
                    const data = $(this.$el).select2('data');
                    this.$emit('input', data.map((item) => {
                        return item.id;
                    }));
                })
        },
        watch: {
            value(value) {
                // update value
                $(this.$el).val(value)
                // .trigger('change')
            },
            options(options) {
                // update options
                $(this.$el).empty().select2({ data: options });
                $(this.$el).val(this.value).trigger('change');
            }
        },
        destroyed() {
            $(this.$el).off().select2('destroy')
        }
    };
</script>

<style scoped>
</style>
