<template>
  <div class="about-page">
    <!-- Header -->
    <header class="about-header">
      <h1>{{ $t('About us') }}</h1>
      <p>{{ $t('about_intro') }}</p>
    </header>

    <!-- Content -->
    <main class="container">
      <section class="section">
        <h2>{{ $t('Who we are') }}</h2>
        <p>{{ $t('about_who_we_are') }}</p>
      </section>

      <section class="section">
        <h2>{{ $t('Our vision') }}</h2>
        <p>{{ $t('about_vision') }}</p>
      </section>

      <section class="section">
        <h2>{{ $t('Our mission') }}</h2>
        <p>{{ $t('about_mission') }}</p>
      </section>

      <section class="section">
        <h2>{{ $t('Our values') }}</h2>
        <div class="values">
          <div class="value-box" v-for="(value, index) in values" :key="index">
            <h3>{{ $t(value.title) }}</h3>
            <p>{{ $t(value.text) }}</p>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

const values = ref([
  {
    title: "value_quality_title",
    text: "value_quality_text",
  },
  {
    title: "value_innovation_title",
    text: "value_innovation_text",
  },
  {
    title: "value_transparency_title",
    text: "value_transparency_text",
  },
  {
    title: "value_customer_title",
    text: "value_customer_text",
  },
]);

onMounted(() => {
  window.scrollTo(0, 0);
});
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap");

.about-page {
  font-family: "Cairo", sans-serif;
  background-color: #fafafa;
  color: #333;
  line-height: 1.8;
}

/* Header */
.about-header {
  background: #111;
  color: #fff;
  text-align: center;
  padding: 50px 20px;
}

.about-header h1 {
  font-size: 36px;
  margin-bottom: 10px;
  letter-spacing: 1px;
}

.about-header p {
  color: #ccc;
  font-size: 16px;
}

/* Content */
.container {
  max-width: 1100px;
  margin: 50px auto;
  padding: 0 20px;
}

.section {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  padding: 30px;
  margin-bottom: 40px;
  transition: 0.3s;
}

.section:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.section h2 {
  color: #222;
  border-right: 5px solid #689c41;
  padding-right: 10px;
  font-size: 24px;
  margin-bottom: 15px;
}

/* Values */
.values {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 15px;
  margin-top: 15px;
}

.value-box {
  background: #f3f8f0;
  border-radius: 10px;
  padding: 15px;
  text-align: center;
  border: 1px solid #e1ebd9;
}

.value-box h3 {
  color: #689c41;
  font-size: 18px;
  margin-bottom: 8px;
}

/* Footer */
.about-footer {
  background: #111;
  color: #ccc;
  text-align: center;
  padding: 25px 15px;
  margin-top: 50px;
  font-size: 14px;
}

.about-footer a {
  color: #fff;
  text-decoration: none;
}
</style>
