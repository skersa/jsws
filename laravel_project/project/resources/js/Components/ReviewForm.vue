<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ review: Object });

const rating = ref(props.review.rating || 5);
const form = useForm({ rating: rating.value, comment: props.review.comment || '' });

function setRating(v) { rating.value = v; form.rating = v; }
function submit() {
    form.rating = rating.value;
    form.post(route('reviews.store', props.review.seller_username), { preserveScroll: true });
}
</script>

<template>
    <div class="ord-box" data-testid="order-review-box" id="orderReviewBox">
        <div class="ord-box-title"><i class="bi bi-star-fill" style="color:#f59e0b"></i> Satıcıyı Değerlendir</div>
        <p class="pf-text-muted-sm" style="margin-bottom:12px">
            {{ review.exists ? 'Değerlendirmeni güncelleyebilirsin.' : `Ürünü teslim aldın! ${review.seller_name} için deneyimini puanla.` }}
        </p>
        <form @submit.prevent="submit" class="rv-order-form">
            <div class="rv-order-stars" style="display:flex;gap:6px;font-size:26px;color:#f59e0b;margin-bottom:12px;cursor:pointer">
                <i v-for="i in 5" :key="i" class="bi" :class="rating >= i ? 'bi-star-fill' : 'bi-star'"
                   :data-val="i" :data-testid="`order-review-star-${i}`" @click="setRating(i)"></i>
            </div>
            <div class="ord-field">
                <textarea v-model="form.comment" rows="3" placeholder="Satıcı, ürün ve kargo hakkında görüşün..." data-testid="order-review-comment"></textarea>
            </div>
            <button type="submit" class="btn-admin-pri" style="width:100%" :disabled="form.processing" data-testid="order-review-submit">
                <i class="bi bi-send me-1"></i> {{ review.exists ? 'Değerlendirmeyi Güncelle' : 'Değerlendir' }}
            </button>
        </form>
    </div>
</template>
