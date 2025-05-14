            const email = checkoutData.email;
			const session_id = checkoutData.session_id;
			const isOTP = checkoutData.isOTP;

			vitalswapCheckout.init({
				session: checkoutData.session_id,
				isOtp: checkoutData.isOTP, 
				email: checkoutData.email, 
				// callback: "https://yourcheckoutpage.com", //Optional URL to redirect after completion
				environment: "sandbox", // Set to "sandbox" or "production"
				onload: () => {
					console.log("Checkout started");
				},
				onsuccess: (data) => {
					console.log("Checkout successful:", data);
				},
				onclose: (data) => {
					console.log("Checkout closed:", data);
				},
				onerror: (error) => {
					console.error("Checkout error:", error);
				},
			});