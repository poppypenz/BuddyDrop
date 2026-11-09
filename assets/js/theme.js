/**
 * BuddyDrop Theme Manager - Lunar Dark Mode (ธีมดวงจันทร์ ดำ-ทอง/ดำ-เหลืองสว่าง)
 */

// ฟังก์ชันหลักในการใช้ธีม
function applyTheme(theme) {
    const isDark = (theme === 'dark');
    const root = document.documentElement;
    const body = document.body;

    if (isDark) {
        root.setAttribute('data-theme', 'dark');
        root.classList.add('dark-mode');
        if (body) {
            body.setAttribute('data-theme', 'dark');
            body.classList.add('dark-mode');
        }
        try {
            localStorage.setItem('buddydrop_theme', 'dark');
        } catch (e) {}
    } else {
        root.removeAttribute('data-theme');
        root.classList.remove('dark-mode');
        if (body) {
            body.removeAttribute('data-theme');
            body.classList.remove('dark-mode');
        }
        try {
            localStorage.setItem('buddydrop_theme', 'light');
        } catch (e) {}
    }

    updateThemeToggleUI(isDark);
}

// ฟังก์ชันสลับธีมเมื่อกดปุ่ม
function toggleTheme() {
    const isCurrentlyDark = document.documentElement.getAttribute('data-theme') === 'dark' || 
                            document.documentElement.classList.contains('dark-mode') ||
                            (document.body && document.body.classList.contains('dark-mode'));
    const nextTheme = isCurrentlyDark ? 'light' : 'dark';
    applyTheme(nextTheme);
}

// ทำให้ toggleTheme อยู่ในระดับ global window แน่นอน 100%
window.toggleTheme = toggleTheme;
window.applyTheme = applyTheme;

// ฟังก์ชันปรับข้อความและไอคอนบนปุ่ม
function updateThemeToggleUI(isDarkOverride) {
    const isDark = (typeof isDarkOverride === 'boolean') 
        ? isDarkOverride 
        : (document.documentElement.getAttribute('data-theme') === 'dark' || document.documentElement.classList.contains('dark-mode'));

    const buttons = document.querySelectorAll('.theme-toggle-btn');
    buttons.forEach(btn => {
        const icon = btn.querySelector('.theme-toggle-icon');
        const text = btn.querySelector('.theme-toggle-text');

        if (icon) {
            icon.textContent = isDark ? '☀️' : '🌙';
        }
        if (text) {
            text.textContent = isDark ? 'ธีมสว่าง' : 'ธีมดวงจันทร์';
        }

        btn.setAttribute('title', isDark ? 'คลิกเพื่อสลับเป็น ธีมสว่าง ☀️' : 'คลิกเพื่อสลับเป็น ธีมดวงจันทร์ (ดำ-ทอง) 🌙');

        if (isDark) {
            btn.classList.add('is-dark');
        } else {
            btn.classList.remove('is-dark');
        }
    });
}

// 1. ตรวจสอบค่าที่เซฟไว้และนำไปใช้ทันทีเพื่อป้องกันแสงกระพริบ
(function initTheme() {
    let saved = null;
    try {
        saved = localStorage.getItem('buddydrop_theme');
    } catch (e) {}
    if (saved === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        document.documentElement.classList.add('dark-mode');
    }
})();

// 2. เมื่อหน้าเว็บโหลดโครงสร้างเสร็จ ให้เชื่อมต่อกับ body และอัปเดต UI ปุ่ม
document.addEventListener('DOMContentLoaded', function () {
    let saved = null;
    try {
        saved = localStorage.getItem('buddydrop_theme');
    } catch (e) {}
    if (saved === 'dark') {
        applyTheme('dark');
    } else {
        updateThemeToggleUI(false);
    }
});
