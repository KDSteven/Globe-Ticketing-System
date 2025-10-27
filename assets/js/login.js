const {createApp} = Vue;

createApp({
    data() {
        return {
            email: '',
            password: '',
            error: 'ergergerge'
        };
    },
    methods: {
        async login() {
            if (!this.email || !this.password) {
                this.error = "PLease fill in all fields.";
                return; 
            }

            const response  = await fetch('api/login.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    email: this.email,
                    password: this.password
                })
            });
            const result = await response.json();

            if(result.ok) {
                window.location.href = "dashboard.php";
            } else {
                this.error = result.error || "Invalid Credentials"
            }
        }
    }
}).mount("#loginApp");