import { UserPlus, CheckCircle, MessageSquare } from "lucide-react"

const steps = [
  {
    number: "1",
    icon: UserPlus,
    title: "Register",
    description: "Create your account as a buyer or supplier. Quick and easy registration process.",
  },
  {
    number: "2",
    icon: CheckCircle,
    title: "Verify",
    description: "Suppliers undergo verification. Buyers can browse verified profiles with confidence.",
  },
  {
    number: "3",
    icon: MessageSquare,
    title: "Connect",
    description: "Start secure conversations and build lasting business relationships worldwide.",
  },
]

export function HowItWorksSection() {
  return (
    <section className="py-16 md:py-24 bg-primary text-primary-foreground">
      <div className="container mx-auto px-4">
        <div className="text-center mb-12">
          <h2 className="text-3xl md:text-4xl font-bold mb-4">How It Works</h2>
          <p className="text-lg text-primary-foreground/90 max-w-2xl mx-auto">
            Three simple steps to connect with verified suppliers and grow your business
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
          {steps.map((step, index) => (
            <div key={index} className="relative">
              <div className="flex flex-col items-center text-center">
                <div className="mb-4 relative">
                  <div className="w-20 h-20 bg-secondary rounded-full flex items-center justify-center">
                    <step.icon className="h-10 w-10 text-secondary-foreground" />
                  </div>
                  <div className="absolute -top-2 -right-2 w-8 h-8 bg-primary-foreground text-primary rounded-full flex items-center justify-center font-bold">
                    {step.number}
                  </div>
                </div>
                <h3 className="text-2xl font-bold mb-3">{step.title}</h3>
                <p className="text-primary-foreground/90 leading-relaxed">{step.description}</p>
              </div>

              {/* Connector Line (hidden on mobile, shown on desktop between steps) */}
              {index < steps.length - 1 && (
                <div className="hidden md:block absolute top-10 left-[60%] w-[80%] h-0.5 bg-primary-foreground/30" />
              )}
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
