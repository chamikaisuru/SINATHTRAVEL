import { useLanguage } from "@/lib/i18n";
import { Badge } from "@/components/ui/badge";
import { CheckCircle2 } from "lucide-react";
import { images } from "@/lib/data";

export default function About() {
  const { t } = useLanguage();

  return (
    <div className="py-12 md:py-20">
      <div className="container mx-auto px-4">
        {/* Header */}
        <div className="text-center max-w-3xl mx-auto mb-16 space-y-4">
          <Badge variant="outline" className="border-secondary text-secondary font-bold uppercase tracking-widest px-4 py-1">
            Who We Are
          </Badge>
          <h1 className="text-4xl md:text-5xl font-heading font-extrabold text-primary">
            {t.about.title}
          </h1>
          <p className="text-xl text-muted-foreground leading-relaxed">
            {t.about.content}
          </p>
        </div>

        {/* Content Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-16 items-center mb-24">
          <div className="relative">
             <div className="absolute -inset-4 bg-primary/5 rounded-[2rem] -z-10 transform -rotate-2"></div>
             <img 
               src={images.visa} 
               alt="Our office" 
               className="rounded-2xl shadow-2xl w-full object-cover"
             />
             <div className="absolute -bottom-8 -right-8 bg-white p-6 rounded-xl shadow-xl border border-border max-w-xs hidden md:block">
               <div className="text-4xl font-bold text-secondary mb-1">10+</div>
               <div className="text-muted-foreground font-medium">Years of Excellence in Travel & Tourism</div>
             </div>
          </div>
          
          <div className="space-y-8">
            <div>
              <h2 className="text-3xl font-bold text-primary mb-4">Licensed & Accredited</h2>
              <p className="text-muted-foreground text-lg">
                We are proud to be fully licensed by the Civil Aviation Authority of Sri Lanka and an IATA accredited agent. This certification guarantees our adherence to the highest international standards of service and financial security.
              </p>
            </div>

            <div className="bg-primary/5 p-6 rounded-xl border border-primary/10">
              <h3 className="text-xl font-bold text-primary mb-4 flex items-center gap-2">
                <CheckCircle2 className="text-secondary" /> Our Commitment
              </h3>
              <p className="text-muted-foreground">
                At Sinath Travels, we don't just sell tickets; we create experiences. Our team of dedicated travel consultants works tirelessly to ensure every aspect of your journey is planned to perfection.
              </p>
            </div>

            <div className="grid grid-cols-2 gap-4">
               <div className="bg-white p-4 rounded-lg shadow-sm border border-border text-center">
                 <div className="font-bold text-2xl text-primary mb-1">IATA</div>
                 <div className="text-xs text-muted-foreground uppercase tracking-wider">Accredited</div>
               </div>
               <div className="bg-white p-4 rounded-lg shadow-sm border border-border text-center">
                 <div className="font-bold text-2xl text-primary mb-1">CAA</div>
                 <div className="text-xs text-muted-foreground uppercase tracking-wider">Licensed</div>
               </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
