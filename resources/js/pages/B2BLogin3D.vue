<template>
  <div
    class="relative flex items-center justify-center min-h-screen bg-gradient-to-br from-white via-gray-50 to-gray-100 overflow-hidden"
  >
    <!-- خلفية 3D -->
    <div ref="threeRoot" class="absolute inset-0 -z-10"></div>

    <!-- كارت تسجيل الدخول -->
    <div
      class="relative z-10 w-full max-w-md bg-white/70 backdrop-blur-lg rounded-3xl shadow-2xl border border-gray-100 p-8 mx-4"
    >
      <div class="flex flex-col items-center mb-6">
        <img
          src="https://ecommerce.city2tec.com/storage/logo/OYfJ2vAWf6D9QFbzo4ZysxxTskQcWMz5tg0BeCCn.png"
          alt="Logo"
          class="w-24 mb-4 drop-shadow-md"
        />
        <h2 class="text-3xl font-bold text-[#537d34]">تسجيل الدخول</h2>
        <p class="text-gray-500 text-sm mt-2">
          مرحبًا بك في منصة البيع بالجملة الخاصة بنا 👋
        </p>
      </div>

      <form @submit.prevent="onSubmit" class="space-y-5">
        <div>
          <label class="block text-sm text-gray-700 mb-1">البريد الإلكتروني</label>
          <input
            v-model="form.email"
            type="email"
            placeholder="example@company.com"
            required
            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#537d34]/40"
          />
        </div>

        <div>
          <label class="block text-sm text-gray-700 mb-1">كلمة المرور</label>
          <input
            v-model="form.password"
            type="password"
            placeholder="••••••••"
            required
            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#537d34]/40"
          />
        </div>

        <button
          type="submit"
          class="w-full py-3 rounded-xl text-white font-semibold bg-[#537d34] hover:bg-[#46682c] transition-all shadow-md transform hover:scale-[1.02]"
        >
          دخول
        </button>
      </form>

      <p class="mt-6 text-center text-sm text-gray-500">
        ليس لديك حساب؟ <a href="#" class="text-[#537d34] hover:underline">أنشئ حسابًا الآن</a>
      </p>
    </div>
  </div>
</template>

<script setup>
import * as THREE from "three";
import { onMounted, ref, reactive } from "vue";

const threeRoot = ref(null);
const form = reactive({
  email: "",
  password: "",
});

const onSubmit = () => {
  alert(`تم إدخال: ${form.email}`);
};

onMounted(() => {
  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(
    75,
    window.innerWidth / window.innerHeight,
    0.1,
    1000
  );
  const renderer = new THREE.WebGLRenderer({ alpha: true });
  renderer.setSize(window.innerWidth, window.innerHeight);
  threeRoot.value.appendChild(renderer.domElement);

  const geometry = new THREE.TorusGeometry(10, 3, 16, 100);
  const material = new THREE.MeshStandardMaterial({
    color: 0x537d34,
    emissive: 0x233d12,
    metalness: 0.4,
    roughness: 0.3,
  });
  const torus = new THREE.Mesh(geometry, material);
  scene.add(torus);

  const pointLight = new THREE.PointLight(0xffffff);
  pointLight.position.set(10, 10, 10);
  scene.add(pointLight);

  camera.position.z = 25;

  const animate = () => {
    requestAnimationFrame(animate);
    torus.rotation.x += 0.005;
    torus.rotation.y += 0.008;
    renderer.render(scene, camera);
  };
  animate();

  window.addEventListener("resize", () => {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
  });
});
</script>
