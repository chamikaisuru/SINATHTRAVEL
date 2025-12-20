import { useLanguage } from "@/lib/i18n";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Card, CardContent } from "@/components/ui/card";
import { MapPin, Phone, Mail } from "lucide-react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { useToast } from "@/hooks/use-toast";
import { submitInquiry } from "@/lib/api";
import { useState } from "react";

const contactSchema = z.object({
  name: z.string().min(2, "Name is required"),
  email: z.string().email("Invalid email address"),
  phone: z.string().min(9, "Valid phone number is required"),
  message: z.string().min(10, "Message must be at least 10 characters"),
});

type ContactFormData = z.infer<typeof contactSchema>;

export default function Contact() {
  const { t } = useLanguage();
  const { toast } = useToast();
  const [isSubmitting, setIsSubmitting] = useState(false);
  
  const form = useForm<ContactFormData>({
    resolver: zodResolver(contactSchema),
    defaultValues: {
      name: "",
      email: "",
      phone: "",
      message: "",
    },
  });

  async function onSubmit(values: ContactFormData) {
    setIsSubmitting(true);
    
    try {
      await submitInquiry(values);
      
      toast({
        title: "Inquiry Sent!",
        description: "We have received your message and will contact you shortly.",
      });
      
      form.reset();
    } catch (error) {
      console.error('Submission error:', error);
      toast({
        title: "Submission Failed",
        description: error instanceof Error ? error.message : "Please try again later.",
        variant: "destructive",
      });
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <div className="min-h-screen bg-muted/30">
      {/* Header */}
      <div className="bg-primary py-20 text-center text-white">
        <h1 className="text-4xl md:text-5xl font-heading font-bold mb-4">{t.contact.title}</h1>
        <p className="text-primary-foreground/80 text-lg max-w-xl mx-auto px-4">
          Get in touch with us for all your travel needs. We are here to help you plan your next adventure.
        </p>
      </div>

      <div className="container mx-auto px-4 py-16 -mt-10">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Info Cards */}
          <div className="space-y-6 lg:col-span-1">
            <Card className="shadow-lg border-l-4 border-l-secondary">
              <CardContent className="p-6 flex items-start gap-4">
                <MapPin className="w-6 h-6 text-secondary shrink-0 mt-1" />
                <div>
                  <h3 className="font-bold text-lg text-primary mb-2">Visit Us</h3>
                  <p className="text-muted-foreground">
                    Main Street, <br />
                    Akkaraipattu, <br />
                    Sri Lanka
                  </p>
                </div>
              </CardContent>
            </Card>

            <Card className="shadow-lg border-l-4 border-l-secondary">
              <CardContent className="p-6 flex items-start gap-4">
                <Phone className="w-6 h-6 text-secondary shrink-0 mt-1" />
                <div>
                  <h3 className="font-bold text-lg text-primary mb-2">Call Us</h3>
                  <p className="text-muted-foreground mb-1">0117 794 128</p>
                  <p className="text-muted-foreground text-sm">Mon - Sat, 8am - 6pm</p>
                </div>
              </CardContent>
            </Card>

            <Card className="shadow-lg border-l-4 border-l-secondary">
              <CardContent className="p-6 flex items-start gap-4">
                <Mail className="w-6 h-6 text-secondary shrink-0 mt-1" />
                <div>
                  <h3 className="font-bold text-lg text-primary mb-2">Email Us</h3>
                  <p className="text-muted-foreground break-all">info@sinathtravels.com</p>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Contact Form */}
          <Card className="shadow-xl lg:col-span-2 border-t-4 border-t-primary">
            <CardContent className="p-8">
              <h2 className="text-2xl font-bold text-primary mb-6">Send us a Message</h2>
              <Form {...form}>
                <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <FormField
                      control={form.control}
                      name="name"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Name</FormLabel>
                          <FormControl>
                            <Input 
                              placeholder="Your Name" 
                              {...field} 
                              className="bg-muted/50" 
                              disabled={isSubmitting}
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <FormField
                      control={form.control}
                      name="email"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>{t.contact.email}</FormLabel>
                          <FormControl>
                            <Input 
                              placeholder="your@email.com" 
                              {...field} 
                              className="bg-muted/50" 
                              disabled={isSubmitting}
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                  </div>
                  
                  <FormField
                    control={form.control}
                    name="phone"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>{t.contact.phone}</FormLabel>
                        <FormControl>
                          <Input 
                            placeholder="Your Phone Number" 
                            {...field} 
                            className="bg-muted/50" 
                            disabled={isSubmitting}
                          />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={form.control}
                    name="message"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>{t.contact.message}</FormLabel>
                        <FormControl>
                          <Textarea 
                            placeholder="How can we help you?" 
                            className="min-h-[150px] bg-muted/50" 
                            {...field} 
                            disabled={isSubmitting}
                          />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />

                  <Button 
                    type="submit" 
                    size="lg" 
                    className="w-full md:w-auto bg-secondary hover:bg-secondary/90"
                    disabled={isSubmitting}
                  >
                    {isSubmitting ? "Sending..." : t.contact.send}
                  </Button>
                </form>
              </Form>
            </CardContent>
          </Card>
        </div>

        {/* Map Embed */}
        <div className="mt-16 h-[400px] rounded-2xl overflow-hidden shadow-lg border border-border">
          <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d63320.43002237937!2d81.82!3d7.2!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae515f426090623%3A0x6283b40092289659!2sAkkaraipattu%2C%20Sri%20Lanka!5e0!3m2!1sen!2sus!4v1650000000000!5m2!1sen!2sus" 
            width="100%" 
            height="100%" 
            style={{ border: 0 }} 
            allowFullScreen 
            loading="lazy" 
            referrerPolicy="no-referrer-when-downgrade"
            title="Location Map"
          ></iframe>
        </div>
      </div>
    </div>
  );
}